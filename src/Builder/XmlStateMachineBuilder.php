<?php

namespace Workflux\Builder;

use Workflux\Transition\Transition;
use Workflux\State\State;
use Workflux\State\StateInterface;
use Workflux\Guard\GuardInterface;
use Workflux\Error\Error;
use Workflux\StateMachine\StateMachine;
use Workflux\Parser\Xml\StateMachineDefinitionParser;

/**
 * The XmlStateMachineBuilder can build/load a state_machine from a given xml file,
 * containing valid state machine declarations.
 */
class XmlStateMachineBuilder extends StateMachineBuilder
{
    /**
     * Verifies the builder's current state and builds a state machine off of it.
     *
     * @return \Workflux\StateMachine\StateMachineInterface
     */
    public function build()
    {
        $state_machine_definition = $this->resolveStateMachineDefinition();

        $this->setStateMachineName($state_machine_definition['name']);
        if (isset($state_machine_definition['class'])) {
            $this->setStateMachineClass($state_machine_definition['class']);
        }

        foreach ($state_machine_definition['states'] as $state_name => $state_definition) {
            $this->addState($this->createState($state_definition));
            $this->addEventTransitions($state_definition);
            $this->addSequentialTransitions($state_definition);
        }

        return parent::build();
    }

    /**
     * Returns the appropiate parsed state machine definition to use for building the state machine.
     *
     * @return array
     */
    protected function resolveStateMachineDefinition()
    {
        $state_machine_definitions = $this->parseStateMachineDefitions();
        if (!$this->hasOption('name')) {
            throw new Error("Missing required state machine name.");
        }

        $name = $this->getOption('name');
        if (isset($state_machine_definitions[$name])) {
            $state_machine_definition = $state_machine_definitions[$name];
        } else {
            throw new Error(
                sprintf('Unable to find configured state machine with name "%s". Maybe a type?', $name)
            );
        }

        return $state_machine_definition;
    }

    /**
     * Parses the configured state machine definition file and returns all parsed state machine definitions.
     *
     * @return array An assoc array with name of a state machine as key and the state machine def as value.
     */
    protected function parseStateMachineDefitions()
    {
        $state_machine_definition_file = $this->getOption('state_machine_definition');

        $parser = new StateMachineDefinitionParser();

        return $parser->parse($state_machine_definition_file);
    }

    /**
     * Creates a concrete StateInterface instance based on the given state definition.
     *
     * @param array $state_definition
     *
     * @return StateInterface
     */
    protected function createState(array $state_definition)
    {
        $state_implementor = isset($state_definition['class']) ? $state_definition['class'] : State::CLASS;
        $this->loadStateImplementor($state_implementor);

        $state = new $state_implementor(
            $state_definition['name'],
            $state_definition['type'],
            $state_definition['options']
        );

        if (!$state instanceof StateInterface) {
            throw new Error(
                sprintf(
                    'Configured custom implementor for state %s does not implement "%s".',
                    $state_definition['name'],
                    StateInterface::CLASS
                )
            );
        }

        return $state;
    }

    /**
     * Tries to autoload the given state implementor if the class doesn't exist.
     *
     * @param array $state_definition
     *
     * @throws Error If the given state implementor can't be loaded.
     */
    protected function loadStateImplementor($state_implementor)
    {
        if (!class_exists($state_implementor, true)) {
            throw new Error(
                sprintf(
                    'Unable to load configured custom state implementor "%s".',
                    $state_implementor
                )
            );
        }
    }

    /**
     * Creates a list of event transitions from the given state definition
     * and adds them to the builder's current state machine setup.
     *
     * @param array $state_definition
     */
    protected function addEventTransitions(array $state_definition)
    {
        foreach ($state_definition['events'] as $event_name => $event_definition) {
            if ($event_name === StateMachine::SEQ_TRANSITIONS_KEY) {
                continue;
            }
            foreach ($event_definition['transitions'] as $transition_definition) {
                $this->addTransition(
                    $this->createTransition($state_definition['name'], $transition_definition),
                    $event_name
                );
            }
        }
    }

    /**
     * Creates a list of sequential transitions from the given state definition
     * and adds them to the builder's current state machine setup.
     *
     * @param array $state_definition
     */
    protected function addSequentialTransitions(array $state_definition)
    {
        foreach ($state_definition['events'][StateMachine::SEQ_TRANSITIONS_KEY] as $transition_definition) {
            $this->addTransition(
                $this->createTransition($state_definition['name'], $transition_definition)
            );
        }
    }

    /**
     * Creates a state transition from the given transition definition.
     *
     * @param string $state_name
     * @param array $transition_definition
     *
     * @return \Workflux\Transition\TransitionInterface
     */
    protected function createTransition($state_name, array $transition_definition)
    {
        $target = $transition_definition['outgoing_state_name'];
        $guard_definition = $transition_definition['guard'];

        $guard = null;
        if ($guard_definition) {
            $guard = new $guard_definition['class']($guard_definition['options']);

            if (!$guard instanceof GuardInterface) {
                throw new Error(
                    sprintf("Configured guard classes must implement %s.", GuardInterface::CLASS)
                );
            }
        }

        return new Transition($state_name, $target, $guard);
    }
}
