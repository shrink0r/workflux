<?php

namespace Workflux\Builder;

use Workflux\Transition\Transition;
use Workflux\State\State;
use Workflux\Guard\IGuard;
use Workflux\Parser\Xml\StateMachineDefinitionParser;

class XmlStateMachineBuilder extends StateMachineBuilder
{
    public function build()
    {
        $state_machine_definition_file = $this->getOption('state_machine_definition');

        $parser = new StateMachineDefinitionParser();
        $state_machine_definition = $parser->parse($state_machine_definition_file);

        $this->setStateMachineName($state_machine_definition['name']);
        foreach ($state_machine_definition['states'] as $state_name => $state_definition) {
            $state = new State($state_name, $state_definition['type']);
            $this->addState($state);

            foreach ($state_definition['events'] as $event_name => $event_definition) {
                foreach ($event_definition['transitions'] as $transition_definition) {
                    $target = $transition_definition['outgoing_state_name'];
                    $guard_definition = $transition_definition['guard'];

                    $guard = null;
                    if ($guard_definition) {
                        $guard = new $guard_definition['class']($guard_definition['options']);

                        if (!$guard instanceof IGuard) {
                            throw new Error("Configured guard classes must implement " . IGuard::CLASS);
                        }
                    }

                    $transition = new Transition($state_name, $target, $guard);
                    $this->addTransition($event_name, $transition);
                }
            }
        }

        return parent::build();
    }
}
