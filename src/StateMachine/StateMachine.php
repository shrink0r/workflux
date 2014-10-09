<?php

namespace Workflux\StateMachine;

use Workflux\Error\Error;
use Workflux\StatefulSubjectInterface;
use Workflux\State\StateInterface;
use Workflux\Transition\TransitionInterface;

class StateMachine implements StateMachineInterface
{
    const SEQ_TRANSITIONS_KEY = '_sequential';

    protected $name;

    protected $states;

    protected $transitions;

    protected $initial_state;

    protected $final_states;

    protected $event_states;

    public function __construct($name, array $states, array $transitions)
    {
        $this->name = $name;
        $this->states = $states;
        $this->transitions = $transitions;

        $this->initial_state = null;
        $this->final_states = [];
        $this->event_states = [];

        foreach ($this->states as $state) {
            $this->mapState($state);
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
        if ($state_or_state_name instanceof StateInterface) {
            $state_name = $state_or_state_name->getName();
        }

        foreach ($this->event_states as $event_state) {
            if ($event_state->getName() === $state_name) {
                return true;
            }
        }
        return false;
    }

    public function execute(StatefulSubjectInterface $subject, $event_name)
    {
        $current_state = $this->getValidStartStateFor($subject);

        do {
            $next_state = $this->getNextState($subject, $current_state, $event_name);
            $current_state->onExit($subject);
            $next_state->onEntry($subject);
            $current_state = $next_state;

            // after the initial event has been processed, the only we to keep going are sequentially chained states
            $event_name = self::SEQ_TRANSITIONS_KEY;
            // so we keep executing until we reach the next event state or the end of the graph.
        } while (!$this->isEventState($current_state) && !$current_state->isFinal());

        return $current_state;
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

    public function getTransitions($state_name = '', $event_name = '')
    {
        $transitions = $this->transitions;

        if (!empty($state_name)) {
            if (!isset($this->transitions[$state_name])) {
                throw new Error(sprintf('No transitions available at state "%s".', $state_name));
            }
            $transitions = $this->transitions[$state_name];
        }

        if (!empty($event_name)) {
            if (!isset($transitions[$event_name])) {
                throw new Error(
                    sprintf('No transitions available for event "%s" at state "%s".', $event_name, $state_name)
                );
            }
            $transitions = $transitions[$event_name];
        }

        return $transitions;
    }

    protected function mapState(StateInterface $state)
    {
        switch ($state->getType()) {
            case StateInterface::TYPE_FINAL:
                $this->final_states[] = $state;
                break;
            case StateInterface::TYPE_INITIAL:
                $this->initial_state = $state;
            default:
                $state_transitions = $this->getTransitions($state->getName());
                if (!isset($state_transitions[StateMachine::SEQ_TRANSITIONS_KEY])) {
                    $this->event_states[] = $state;
                }
        }
    }

    protected function getValidStartStateFor(StatefulSubjectInterface $subject)
    {
        $start_state = $this->getStateOrFail(
            $subject->getExecutionContext()->getCurrentStateName()
        );

        if (!$this->isEventState($start_state)) {
            throw new Error(
                sprintf(
                    "Current execution is pointing to an invalid state %s." .
                    " The state machine execution must be started and resume by entering an event state.",
                    $start_state->getName()
                )
            );
        }

        return $start_state;
    }

    protected function getNextState(StatefulSubjectInterface $subject, StateInterface $current_state, $event_name)
    {
        $accepted_transition = $this->getActivatedTransition($subject, $current_state, $event_name);
        if (!$accepted_transition) {
            throw new Error(
                sprintf(
                    'Transition for event "%s" at state "%s" was rejected.',
                    $event_name,
                    $current_state->getName()
                )
            );
        }

        return $this->getStateOrFail($accepted_transition->getOutgoingStateName());
    }

    protected function getActivatedTransition(StatefulSubjectInterface $subject, StateInterface $state, $event_name)
    {
        $accepted_transition = null;
        $possible_transitions = $this->getTransitions($state->getName(), $event_name);

        foreach ($possible_transitions as $state_transition) {
            if (!$this->mayProceed($subject, $state_transition)) {
                continue;
            }

            if ($accepted_transition) {
                throw new Error(
                    sprintf('Only one transition is allowed to be active at a time.', $event_name, $state->getName())
                );
            }

            $accepted_transition = $state_transition;
        }

        return $accepted_transition;
    }

    protected function mayProceed(StatefulSubjectInterface $subject, TransitionInterface $transition)
    {
        if (!$transition->hasGuard()) {
            return true;
        }

        return $transition->getGuard()->accept($subject);
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
