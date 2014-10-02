<?php

namespace Workflux\StateMachine;

use Workflux\State\IState;
use Workflux\Transition\ITransition;
use Workflux\Error;

class StateMachineBuilder implements IStateMachineBuilder
{
    protected $options;

    protected $states;

    protected $transitions;

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->states = [];
        $this->transitions = [];
    }

    public function setStateMachineName($state_machine_name)
    {
        $name_regex = '/[a-zA-Z0-9_]+/';

        if (!preg_match($name_regex, $state_machine_name)) {
            throw new Error(
                sprintf(
                    'Invalid statemachine name "%s" given.' .
                    ' Only letters, digits and unserscore are permitted.',
                    $state_machine_name
                )
            );
        }

        $this->state_machine_name = $state_machine_name;

        return $this;
    }

    public function addState(IState $state)
    {
        $state_name = $state->getName();

        if (isset($this->states[$state_name])) {
            throw new Error(
                sprintf(
                    'A state with the name "%s" already has been added.' .
                    ' State names must be unique within each StateMachine.',
                    $state_name
                )
            );
        }

        $this->states[$state_name] = $state;

        return $this;
    }

    public function addStates(array $states)
    {
        foreach ($states as $state) {
            $this->addState($state);
        }

        return $this;
    }

    public function addTransition(ITransition $transition)
    {
        $transition_name = $transition->getName();

        foreach ($transition->getIncomingStateNames() as $incoming_state_name) {
            if (!isset($this->transitions[$incoming_state_name])) {
                $this->transitions[$incoming_state_name] = [ $transition->getName() => $transition ];
            } elseif (isset($this->transitions[$incoming_state_name][$transition->getName()])) {
                throw new Error(
                    sprintf(
                        'A transition with the name "%s" already has been added for state "%s".' .
                        ' Transition names must be unique within the context of a given state.',
                        $transition->getName(),
                        $incoming_state_name
                    )
                );
            } else {
                $this->transitions[$incoming_state_name][$transition->getName()] = $transition;
            }
        }

        return $this;
    }

    public function addTransitions(array $transitions)
    {
        foreach ($transitions as $transition) {
            $this->addTransition($transition);
        }

        return $this;
    }

    public function createStateMachine()
    {
        $this->verifyStateGraph();

        $state_machine_class = isset($this->options['state_machine_class'])
            ? $this->options['state_machine_class']
            : StateMachine::CLASS;

        if (!class_exists($state_machine_class)) {
            throw new Error(
                sprintf('Unable to load state machine class "%s".', $state_machine_class)
            );
        }

        $state_machine = new $state_machine_class($this->state_machine_name, $this->states, $this->transitions);

        if (!$state_machine instanceof IStateMachine) {
            throw new Error(
                sprintf(
                    'The given state machine class "%s" does not implement the required interface "%s"',
                    $state_machine_class,
                    IStateMachine::CLASS
                )
            );
        }

        return $state_machine;
    }

    protected function verifyStateGraph()
    {
        if (!$this->state_machine_name) {
            throw new Error('Required state machine name is missing. Make sure to call setStateMachineName');
        }

        $initial_state = null;
        $final_states = [];

        foreach ($this->states as $state_name => $state) {
            if ($state->isInitial()) {
                if (!$initial_state) {
                    $initial_state = $state;
                } else {
                    throw new Error(
                        sprintf(
                            'Only one initial state is supported per state machine definition.' .
                            'State "%s" has been previously registered as initial state, so state "%" cant be added.',
                            $initial_state->getName(),
                            $state_name
                        )
                    );
                }
            } elseif ($state->isFinal()) {
                $final_states[] = $state;
            }
        }

        foreach ($this->transitions as $state_name => $transitions) {
            if (!isset($this->states[$state_name])) {
                throw new Error(
                    sprintf('Unable to find state "%s" for given set of transitions. Maybe a typo?', $state_name)
                );
            }

            $state_transitions = $this->states[$state_name];
            foreach ($state_transitions as $transition_name => $transition) {
                $incoming_state_names = $transition->getIncomingStateNames();
                if (!in_array($state_name, $incoming_state_names)) {
                    throw new Error(
                        sprintf(
                            'Transition "%s" given for state "%s" does not list the latter as an incoming state.',
                            $transition_name,
                            $state_name
                        )
                    );
                }

                $outgoing_state_name = $transition->getOutgoingStateName();
                if (!isset($this->states[$outgoing_state_name])) {
                    throw new Error(
                        sprintf(
                            'Unable to find outgoing state "%s" for transition "%s". Maybe a typo?',
                            $outgoing_state_name,
                            $transition_name
                        )
                    );
                }
            }
        }

        if (!$initial_state) {
            throw new Error('No state of type "initial" found, but exactly one initial state is required.');
        }

        if (empty($final_states)) {
            throw new Error('No state of type "final" found, but at least one final state is required.');
        }
    }
}
