<?php

namespace Workflux\StateMachine;

use Workflux\Error\Error;
use Workflux\IStatefulSubject;
use Workflux\State\IState;
use Workflux\Transition\ITransition;
use Workflux\Builder\IStateMachineBuilder;

class StateMachine implements IStateMachine
{
    const SEQ_TRANSITIONS_KEY = '_sequential';

    protected $name;

    protected $states;

    protected $transitions;

    protected $initial_state;

    protected $final_states;

    public function __construct($name, array $states, array $transitions)
    {
        $this->name = $name;
        $this->states = $states;
        $this->transitions = $transitions;

        $this->initial_state = null;
        $this->final_states = [];
        $this->event_states = [];

        foreach ($this->states as $state_name => $state) {
            if ($state->isInitial()) {
                $this->initial_state = $state;
            } elseif ($state->isFinal()) {
                $this->final_states[] = $state;
            }

            if (!$state->isFinal()) {
                $state_transitions = $this->getTransitions($state_name);
                if (!isset($state_transitions[StateMachine::SEQ_TRANSITIONS_KEY])
                    && !$state->isFinal()
                ) {
                    $this->event_states[] = $state;
                }
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getInitialState()
    {
        return $this->initial_state;
    }

    public function getFinalStates()
    {
        return $this->final_states;
    }

    public function getEventStates()
    {
        return $this->event_states;
    }

    public function isEventState($state_or_state_name)
    {
        $state_name = $state_or_state_name;
        if ($state_or_state_name instanceof IState) {
            $state_name = $state_or_state_name->getName();
        }

        foreach ($this->event_states as $event_state) {
            if ($event_state->getName() === $state_name) {
                return true;
            }
        }
        return false;
    }

    public function execute(IStatefulSubject $subject, $event_name)
    {
        $current_state = $this->getCurrentStateFor($subject);
        if (!$this->isEventState($current_state)) {
            throw new Error(
                sprintf(
                    "Current execution is pointing to an invalid state %s." .
                    " The state machine execution must be started and resume by entering an event state.",
                    $current_state->getName()
                )
            );
        }

        do {
            $accepted_transition = $this->getAcceptedTransition($subject, $current_state, $event_name);
            if (!$accepted_transition) {
                throw new Error(
                    sprintf(
                        'Transition for event "%s" at state "%s" was rejected.',
                        $event_name,
                        $current_state->getName()
                    )
                );
            }

            $current_state->onExit($subject);
            $current_state = $this->getStateOrFail($accepted_transition->getOutgoingStateName());
            $current_state->onEntry($subject);

            // after the initial event has been processed, the only we to keep going are sequentially chained states
            $event_name = self::SEQ_TRANSITIONS_KEY;
            // so we keep executing until we reach the next event state or the end of the graph.
        } while (!$this->isEventState($current_state) && !$current_state->isFinal());

        return $current_state;
    }

    public function getCurrentStateFor(IStatefulSubject $subject)
    {
        return $this->getStateOrFail(
            $subject->getExecutionContext()->getCurrentStateName()
        );
    }

    public function getStates()
    {
        return $this->states;
    }

    public function getState($state_name)
    {
        $state = null;

        if (isset($this->states[$state_name])) {
            $state = $this->states[$state_name];
        }

        return $state;
    }

    public function getTransitions($state_name = null, $event_name = null)
    {
        $transitions = $this->transitions;

        if ($state_name) {
            if (!isset($this->transitions[$state_name])) {
                throw new Error(
                    sprintf('No transitions available at state "%s".', $state_name)
                );
            }
            $transitions = $this->transitions[$state_name];
        }

        if ($event_name) {
            if (!isset($transitions[$event_name])) {
                throw new Error(
                    sprintf('No transitions available for event "%s" at state "%s".', $event_name, $state_name)
                );
            }
            $transitions = $transitions[$event_name];
        }

        return $transitions;
    }

    protected function getAcceptedTransition(IStatefulSubject $subject, IState $state, $event_name)
    {
        $accepted_transition = null;
        $possible_transitions = $this->getTransitions($state->getName(), $event_name);

        foreach ($possible_transitions as $state_transition) {
            if (!$state_transition->hasGuard() || $state_transition->getGuard()->accept($subject)) {
                if (!$accepted_transition) {
                    $accepted_transition = $state_transition;
                } else {
                    throw new Error(
                        sprintf(
                            'Only one transition is allowed to be active at a time.',
                            $event_name,
                            $state->getName()
                        )
                    );
                }
            }
        }

        return $accepted_transition;
    }

    protected function getStateOrFail($state_name)
    {
        $state = $this->getState($state_name);

        if (!$state) {
            throw new Error(
                sprintf(
                    'Unable to resolve the given state-name "%s" to an existing state.',
                    $state_name
                )
            );
        }

        return $state;
    }
}
