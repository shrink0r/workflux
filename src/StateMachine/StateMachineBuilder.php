<?php

namespace Workflux\StateMachine;

use Workflux\State\IState;
use Workflux\Transition\ITransition;
use Workflux\Error;

class StateMachineBuilder implements IStateMachineBuilder
{
    protected $options;

    protected $state_machine_name;

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
        $name_regex = '/^[a-zA-Z0-9_]+$/';

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

    public function addTransition($event_name, ITransition $transition)
    {
        foreach ($transition->getIncomingStateNames() as $state_name) {
            if (!isset($this->transitions[$state_name])) {
                $this->transitions[$state_name] = [];
            }

            if (!isset($this->transitions[$state_name][$event_name])) {
                $this->transitions[$state_name][$event_name] = [];
            }

            if (in_array($transition, $this->transitions[$state_name][$event_name], true)) {
                throw new Error('Adding the same transition instance twice is not supported.');
            }

            $this->transitions[$state_name][$event_name][] = $transition;
        }

        return $this;
    }

    public function addTransitions(array $events)
    {
        foreach ($events as $event_name => $transition_or_transitions) {
            if (!is_array($transition_or_transitions)) {
                $transitions = [ $transition_or_transitions ];
            } else {
                $transitions = $transition_or_transitions;
            }

            foreach ($transitions as $transition) {
                $this->addTransition($event_name, $transition);
            }
        }

        return $this;
    }

    public function build()
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

        $this->clearInternalState();

        return $state_machine;
    }

    protected function verifyStateGraph()
    {
        if (!$this->state_machine_name) {
            throw new Error('Required state machine name is missing. Make sure to call setStateMachineName.');
        }

        $this->verifyStates();
        $this->verifyTransitions();
    }

    protected function verifyStates()
    {
        $initial_state = null;
        $final_states = [];

        foreach ($this->states as $state_name => $state) {
            $this->verifyState($state, $initial_state, $final_states);
        }

        if (!$initial_state) {
            throw new Error('No state of type "initial" found, but exactly one initial state is required.');
        }

        if (empty($final_states)) {
            throw new Error('No state of type "final" found, but at least one final state is required.');
        }
    }

    protected function verifyState(IState $state, &$initial_state, array &$final_states)
    {
        $state_name = $state->getName();
        $transition_count = isset($this->transitions[$state_name]) ? count($this->transitions[$state_name]) : 0;

        if ($state->isInitial()) {
            if ($initial_state) {
                throw new Error(
                    sprintf(
                        'Only one initial state is supported per state machine definition.' .
                        'State "%s" has been previously registered as initial state, so state "%" cant be added.',
                        $initial_state->getName(),
                        $state_name
                    )
                );
            } else {
                $initial_state = $state;
            }
        } elseif ($state->isFinal()) {
            if ($transition_count > 0) {
                throw new Error(
                    sprintf('State "%s" is final and may not have any transitions.', $state_name)
                );
            }
            $final_states[] = $state;
        } else {
            if ($transition_count === 0) {
                throw new Error(
                    sprintf(
                        'State "%s" is expected to have at least one transition.' .
                        ' Only "%s" states are permitted to have no transitions.',
                        $state_name,
                        IState::TYPE_FINAL
                    )
                );
            }
        }
    }

    protected function verifyTransitions()
    {
        foreach ($this->transitions as $state_name => $state_transitions) {
            if (!isset($this->states[$state_name])) {
                throw new Error(
                    sprintf('Unable to find incoming state "%s" for given transitions. Maybe a typo?', $state_name)
                );
            }

            foreach ($state_transitions as $event_name => $transitions) {
                foreach ($transitions as $transition) {
                    $outgoing_state_name = $transition->getOutgoingStateName();
                    if (!isset($this->states[$outgoing_state_name])) {
                        throw new Error(
                            sprintf(
                                'Unable to find outgoing state "%s" for transition on event "%s". Maybe a typo?',
                                $outgoing_state_name,
                                $event_name
                            )
                        );
                    }
                }
            }
        }
    }

    protected function clearInternalState()
    {
        $this->state_machine_name = null;
        $this->states = [];
        $this->transitions = [];
    }
}
