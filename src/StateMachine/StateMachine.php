<?php

namespace Workflux\StateMachine;

use Workflux\Error;
use Workflux\IStatefulSubject;
use Workflux\State\IState;
use Workflux\Transition\ITransition;

class StateMachine implements IStateMachine
{
    protected $name;

    protected $states;

    protected $transitions;

    public function __construct($name, array $states, array $transitions)
    {
        $this->name = $name;
        $this->states = $states;
        $this->transitions = $transitions;
    }

    public function getName()
    {
        return $this->name;
    }

    public function execute(IStatefulSubject $subject, $transition_name)
    {
        $initial_state = $this->getCurrentStateFor($subject);

        $available_transitions = $this->getTransitions($initial_state->getName());
        if (!in_array($transition_name, array_keys($available_transitions))) {
            throw new Error(
                sprintf(
                    'Unable to find transition %s for state %s.',
                    $transition_name,
                    $initial_state->getName()
                )
            );
        }

        $transition = $this->getTransitionOrFail($initial_state->getName(), $transition_name);

        if ($transition->hasGuard()) {
            $transition_guard = $transition->getGuard();
            if (!$transition_guard->accept($subject)) {
                throw new Error(
                    sprintf(
                        'Applying transition "%s" to state "%s" was rejected by %s.',
                        $transition_name,
                        $initial_state->getName(),
                        get_class($transition_guard)
                    )
                );
            }
        }

        return $this->getStateOrFail($transition->getOutgoingStateName());
    }

    public function getCurrentStateFor(IStatefulSubject $subject)
    {
        return $this->getStateOrFail($subject->getCurrentStateName());
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

    public function getTransitions($state_name = null)
    {
        if ($state_name) {
            $transitions = isset($this->transitions[$state_name]) ? $this->transitions[$state_name] : [];
        } else {
            $transitions = $this->transitions;
        }

        return $transitions;
    }

    public function getTransition($state_name, $transition_name)
    {
        $transition = null;

        $state = $this->getState($state_name);
        if ($state && isset($this->transitions[$state_name])) {
            $state_transitions = $this->transitions[$state_name];
            if (isset($state_transitions[$transition_name])) {
                $transition = $state_transitions[$transition_name];
            }
        }

        return $transition;
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

    protected function getTransitionOrFail($state_name, $transition_name)
    {
        $transition = $this->getTransition($state_name, $transition_name);

        if (!$transition) {
            throw new Error(
                sprintf(
                    'Transition "%s" is not available at state "%s".',
                    $transition_name,
                    $state_name
                )
            );
        }

        return $transition;
    }
}
