<?php

namespace Workflux\Builder;

use Workflux\Transition\Transition;
use Workflux\State\State;
use Workflux\State\StateInterface;
use Workflux\Guard\GuardInterface;
use Workflux\Error\Error;
use Workflux\StateMachine\StateMachine;
use Workflux\Parser\Xml\StateMachineDefinitionParser;

class XmlStateMachineBuilder extends StateMachineBuilder
{
    public function build()
    {
        $state_machine_definition_file = $this->getOption('state_machine_definition');

        $parser = new StateMachineDefinitionParser();
        $state_machine_definitions = $parser->parse($state_machine_definition_file);

        $name = $this->getOption('name', false);
        if (!$name) {
            $state_machine_definition = reset($state_machine_definitions);
        } elseif (isset($state_machine_definitions[$name])) {
            $state_machine_definition = $state_machine_definitions[$name];
        } else {
            throw new Error(
                sprintf('Unable to find configured state machine with name "%s"', $name)
            );
        }

        $this->setStateMachineName($state_machine_definition['name']);

        foreach ($state_machine_definition['states'] as $state_name => $state_definition) {
            $this->addState($this->createState($state_definition));
            $this->addEventTransitions($state_definition);
            $this->addSequentialTransitions($state_definition);
        }

        return parent::build();
    }

    protected function createState(array $state_definition)
    {
        $state_class = isset($state_definition['class']) ? $state_definition['class'] : State::CLASS;
        if (!class_exists($state_class)) {
            throw new Error(
                sprintf(
                    'Unable to load configured custom implementor "%s" for state "%s"',
                    $state_definition['name'],
                    $state_class
                )
            );
        }
        $state = new $state_class($state_definition['name'], $state_definition['type']);

        if (!$state instanceof StateInterface) {
            throw new Error(
                sprintf(
                    'Configured custom implementor for state %s does not implement "%s"',
                    $state_definition['name'],
                    StateInterface::CLASS
                )
            );
        }

        return $state;
    }

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

    protected function addSequentialTransitions(array $state_definition)
    {
        foreach ($state_definition['events'][StateMachine::SEQ_TRANSITIONS_KEY] as $transition_definition) {
            $this->addTransition(
                $this->createTransition($state_definition['name'], $transition_definition)
            );
        }
    }

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
