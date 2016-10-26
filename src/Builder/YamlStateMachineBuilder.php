<?php

namespace Workflux\Builder;

use Shrink0r\Monatic\Maybe;
use Shrink0r\PhpSchema\Error;
use Symfony\Component\Yaml\Parser;
use Workflux\Error\WorkfluxError;
use Workflux\Param\Settings;
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

    private $schema;

    public function __construct(string $yaml_filepath)
    {
        $this->parser = new Parser;
        if (!is_readable($yaml_filepath)) {
            throw new WorkfluxError("Trying to load non-existant statemachine definition at $yaml_filepath");
        }
        $this->yaml_filepath = $yaml_filepath;
        $this->internal_builder = new StateMachineBuilder;
        $this->schema = new StateMachineSchema;
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
            ->build();
    }

    private function createState(string $name, $state): StateInterface
    {
        $s = Maybe::unit($state);
        $state_implementor = $s->class->get() ?: $this->getDefaultStateClass($s);
        if (!class_exists($state_implementor)) {
            throw new WorkfluxError("Trying to create state from non-existant class $state_implementor");
        }
        $state_settings = isset($state['settings']) ? $state['settings'] : [];
        return new $state_implementor($name, new Settings($state_settings));
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
        $implementor = $t->class->get() ?: Transition::CLASS;
        if (!class_exists($implementor)) {
            throw new WorkfluxError("Trying to create transition from non-existant class $state_implementor");
        }
        $constraints = Maybe::unit($transition)->when->get() ?: [];
        $transition_settings = Maybe::unit($transition)->settings->get() ?: [];

        return new $implementor($from, $to, new Settings($transition_settings));
    }
}
