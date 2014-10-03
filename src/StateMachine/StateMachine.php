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

    public function execute(IStatefulSubject $subject, $event_name)
    {
        $current_state = $this->getCurrentStateFor($subject);
        $state_transitions = $this->getTransitions($current_state->getName(), $event_name);

        $accepted_transition = null;
        foreach ($state_transitions as $state_transition) {
            if (!$state_transition->hasGuard() || $state_transition->getGuard()->accept($subject)) {
                if (!$accepted_transition) {
                    $accepted_transition = $state_transition;
                } else {
                    throw new Error(
                        sprintf(
                            'Only one transition is allowed to be active at a time.',
                            $event_name,
                            $current_state->getName()
                        )
                    );
                }
            }
        }

        if (!$accepted_transition) {
            throw new Error(
                sprintf(
                    'Transition for event "%s" at state "%s" was rejected.',
                    $event_name,
                    $current_state->getName()
                )
            );
        }

        $current_state->onExit();
        $next_state = $this->getStateOrFail($accepted_transition->getOutgoingStateName());
        $next_state->onEntry();

        return $next_state;
    }

    public function getCurrentStateFor(IStatefulSubject $subject)
    {
        $execution_state = $subject->getExecutionState();

        return $this->getStateOrFail($execution_state->getCurrentStateName());
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
