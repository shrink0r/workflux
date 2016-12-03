<?php

namespace Workflux\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Yaml\Parser;
use Workflux\Error\WorkfluxError;
use Workflux\Param\Settings;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\State\StateInterface;
use Workflux\Transition\ExpressionConstraint;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionInterface;

final class YamlStateMachineBuilder
{
    const SUFFIX_IN = '-input_schema';

    const SUFFIX_OUT = '-output_schema';

    /**
     * @var Parser $parser
     */
    private $parser;

    /**
     * @var string $yaml_filepath
     */
    private $yaml_filepath;

    /**
     * @var StateMachineBuilder $internal_builder
     */
    private $internal_builder;

    /**
     * @var SchemaInterface $schema
     */
    private $schema;

    /**
     * @var ExpressionLanguage $expression_engine
     */
    private $expression_engine;

    /**
     * @param string $yaml_filepath
     * @param ExpressionLanguage|null $expression_engine
     */
    public function __construct(string $yaml_filepath, ExpressionLanguage $expression_engine = null)
    {
        $this->parser = new Parser;
        if (!is_readable($yaml_filepath)) {
            throw new WorkfluxError("Trying to load non-existant statemachine definition at $yaml_filepath");
        }
        $this->yaml_filepath = $yaml_filepath;
        $this->schema = new StateMachineSchema;
        $this->expression_engine = $expression_engine ?: new ExpressionLanguage;
    }

    /**
     * @return StateMachineInterface
     */
    public function build(): StateMachineInterface
    {
        $this->internal_builder = new StateMachineBuilder;
        $data = $this->parser->parse(file_get_contents($this->yaml_filepath));
        $transitions = [];
        $states = [];
        $result = $this->schema->validate($data);
        if ($result instanceof Error) {
            throw new WorkfluxError('Invalid statemachin configuration given: ' . print_r($result->unwrap(), true));
        }
        foreach ($data['states'] as $name => $state) {
            $states[] = $this->createState($name, $state);
            if (!is_array($state)) {
                continue;
            }
            foreach ($state['transitions'] as $key => $transition) {
                if (is_string($transition)) {
                    $transition = [ 'when' => $transition ];
                }
                $transitions[] = $this->createTransition($name, $key, $transition);
            }
        }
        return $this->internal_builder
            ->addStateMachineName($data['name'])
            ->addStates($states)
            ->addTransitions($transitions)
            ->build(Maybe::unit($data)->class->get() ?: StateMachine::CLASS);
    }

    /**
     * @param string $name
     * @param mixed[]|null $state
     *
     * @return StateInterface
     */
    private function createState(string $name, array $state = null): StateInterface
    {
        $state = Maybe::unit($state);
        $state_implementor = $state->class->get() ?: $this->getDefaultStateClass($state);
        return new $state_implementor(
            $name,
            new Settings($state->settings->get() ?: []),
            $this->createSchema(
                $name.self::SUFFIX_IN,
                $state->input_schema->get()
                ?: [ ':any_name:' => [ 'type' => 'any' ] ]
            ),
            $this->createSchema(
                $name.self::SUFFIX_OUT,
                $state->output_schema->get()
                ?: [ ':any_name:' => [ 'type' => 'any' ] ]
            )
        );
    }

    /**
     * @param string $name
     * @param array $schema_definition
     *
     * @return SchemaInterface
     */
    private function createSchema(string $name, array $schema_definition): SchemaInterface
    {
        return new Schema($name, [ 'type' => 'assoc', 'properties' => $schema_definition ], new Factory);
    }

    /**
     * @param Maybe $state
     *
     * @return string
     */
    private function getDefaultStateClass(Maybe $state): string
    {
        if ($state->initial->get() === true) {
            return InitialState::CLASS;
        } elseif ($state->final->get() === true || $state->get() === null) {
            return FinalState::CLASS;
        }
        return State::CLASS;
    }

    /**
     * @param string $from
     * @param string $to
     * @param  mixed[]|null $transition
     *
     * @return TransitionInterface
     */
    private function createTransition(string $from, string $to, array $transition = null): TransitionInterface
    {
        $t = Maybe::unit($transition);
        if (is_string($t->when->get())) {
            $transition['when'] = [ $t->when->get() ];
        }
        $implementor = $t->class->get() ?: Transition::CLASS;
        $constraints = [];
        foreach (Maybe::unit($transition)->when->get() ?: [] as $expression) {
            if (!is_string($expression)) {
                continue;
            }
            $constraints[] = new ExpressionConstraint($expression, $this->expression_engine);
        }
        $settings = new Settings(Maybe::unit($transition)->settings->get() ?: []);
        return new $implementor($from, $to, $settings, $constraints);
    }
}
