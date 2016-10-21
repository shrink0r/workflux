<?php

namespace Workflux\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use Symfony\Component\Yaml\Parser;
use Workflux\Error\WorkfluxError;
use Workflux\StateMachineInterface;
use Workflux\State\FinalState;
use Workflux\State\InitialState;
use Workflux\State\State;
use Workflux\State\StateInterface;
use Workflux\Transition\Transition;
use Workflux\Transition\TransitionInterface;

final class YamlStateMachineBuilder
{
    private $parser;

    private $yaml_filepath;

    private $internal_builder;

    public function __construct(string $yaml_filepath)
    {
        $this->parser = new Parser;
        if (!is_readable($yaml_filepath)) {
            throw new WorkfluxError("Trying to load non-existant statemachine definition at $yaml_filepath");
        }
        $this->yaml_filepath = $yaml_filepath;
        $this->internal_builder = new StateMachineBuilder;
    }

    /**
     * @param string $state_machine_name
     *
     * @return StateMachineInterface
     */
    public function build(): StateMachineInterface
    {
        $data = $this->parser->parse(file_get_contents($this->yaml_filepath));
        $transitions = [];
        $states = [];
        /* $php_schema = $this->getConfigSchema();
        $result = $php_schema->validate($data);
        if ($result instanceof Error) {
            throw new WorkfluxError('Invalid statemachin configuration given.');
        } */
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
            ->build();
    }

    private function createState(string $name, $state): StateInterface
    {
        $s = Maybe::unit($state);
        $state_implmentor = $s->class->get() ?: $this->getDefaultStateClass($s);
        if (!class_exists($state_implmentor)) {
            throw new WorkfluxError("Trying to create state from non-existant class $state_implmentor");
        }

        return new $state_implmentor($name);
    }

    private function getDefaultStateClass(Maybe $state): string
    {
        if ($state->initial->get() === true) {
            return InitialState::CLASS;
        } elseif ($state->final->get() === true || $state->get() === null) {
            return FinalState::CLASS;
        }

        return State::CLASS;
    }

    private function createTransition(string $from, string $to, $transition): TransitionInterface
    {
        $t = Maybe::unit($transition);
        if (is_string($t->when->get())) {
            $transition['when'] = [ $t->when->get() ];
        }
        $implmentor = $t->class->get() ?: Transition::CLASS;
        if (!class_exists($implmentor)) {
            throw new WorkfluxError("Trying to create transition from non-existant class $state_implmentor");
        }

        foreach (Maybe::unit($transition)->when->get() ?: [] as $constraint) {
            // @todo add support for constraints
        }

        return new $implmentor($from, $to);
    }

    private function getConfigSchema()
    {
        // @todo build php-schema to validate statemachine config structure
    }
}
