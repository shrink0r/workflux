<?php

namespace Workflux\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use Shrink0r\PhpSchema\Factory;
use Shrink0r\PhpSchema\Schema;
use Shrink0r\PhpSchema\SchemaInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Yaml\Parser;
use Workflux\Error\ConfigError;
use Workflux\Error\MissingImplementation;
use Workflux\Param\Settings;
use Workflux\StateMachine;
use Workflux\StateMachineInterface;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\State\StateInterface;
use Workflux\State\Validator;
use Workflux\State\ValidatorInterface;
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
            throw new ConfigError("Trying to load non-existant statemachine definition at: $yaml_filepath");
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
            throw new ConfigError('Invalid statemachine configuration given: ' . print_r($result->unwrap(), true));
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
        $state_implementor = $this->resolveStateImplementor($state);
        $settings = $state->settings->get() ?: [];
        $settings['output'] = array_merge($state->settings->output->get() ?: [], $state->output->get() ?: []);
        $state_instance = new $state_implementor(
            $name,
            new Settings($settings),
            $this->createValidator($name, $state),
            $this->expression_engine
        );
        if ($state->final->get() && !$state_instance->isFinal()) {
            throw new ConfigError("Trying to provide custom state that isn't final but marked as final in config.");
        }
        if ($state->initial->get() && !$state_instance->isInitial()) {
            throw new ConfigError("Trying to provide custom state that isn't initial but marked as initial in config.");
        }
        if ($state->interactive->get() && !$state_instance->isInteractive()) {
            throw new ConfigError(
                "Trying to provide custom state that isn't interactive but marked as interactive in config."
            );
        }
        return $state_instance;
    }

    /**
     * @param Maybe $state
     *
     * @return string
     */
    private function resolveStateImplementor(Maybe $state): string
    {
        switch (true) {
            case $state->initial->get():
                $state_implementor = InitialState::CLASS;
                break;
            case $state->final->get() === true || $state->get() === null: // cast null to final-state by convention
                $state_implementor = FinalState::CLASS;
                break;
            case $state->interactive->get():
                $state_implementor = Interactive::CLASS;
                break;
            default:
                $state_implementor = State::CLASS;
        }
        $state_implementor = $state->class->get() ?? $state_implementor;
        if (!in_array(StateInterface::CLASS, class_implements($state_implementor))) {
            throw new MissingImplementation(
                'Trying to build statemachine that does not implement required ' . StateInterface::CLASS
            );
        }
        return $state_implementor;
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
        $implementor = $t->class->get() ?? Transition::CLASS;
        if (!in_array(TransitionInterface::CLASS, class_implements($implementor))) {
            throw new MissingImplementation(
                'Trying to create transition without implementing required ' . TransitionInterface::CLASS
            );
        }
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

    /**
     * @param string $name
     * @param  Maybe $state
     *
     * @return ValidatorInterface
     */
    private function createValidator(string $name, Maybe $state): ValidatorInterface
    {
        return new Validator(
            $this->createSchema(
                $name.self::SUFFIX_IN,
                $state->input_schema->get()
                ?? [ ':any_name:' => [ 'type' => 'any' ] ]
            ),
            $this->createSchema(
                $name.self::SUFFIX_OUT,
                $state->output_schema->get()
                ?? [ ':any_name:' => [ 'type' => 'any' ] ]
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
}
