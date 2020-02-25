<?php

namespace Workflux\StateMachine;

use Workflux\Error\Error;
use Workflux\StatefulSubjectInterface;
use Workflux\State\StateInterface;
use Workflux\Transition\TransitionInterface;

/**
 * General default implementation of the StateMachineInterface.
 */
class StateMachine implements StateMachineInterface
{
    /**
     * @var string SEQ_TRANSITIONS_KEY
     */
    const SEQ_TRANSITIONS_KEY = '_sequential';

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var array $states
     */
    protected $states;

    /**
     * @var array $transitions
     */
    protected $transitions;

    /**
     * @var StateInterface $initial_state
     */
    protected $initial_state;

    /**
     * @var array $final_states
     */
    protected $final_states;

    /**
     * @var array $event_states
     */
    protected $event_states;

    /**
     * Creates a new StateMachine instance.
     *
     * @param string $name
     * @param array $states
     * @param array $transitions
     */
    public function __construct($name, array $states, array $transitions)
    {
        $this->name = $name;
        $this->states = $states;
        $this->transitions = $transitions;

        $this->final_states = [];
        $this->event_states = [];

        foreach ($this->states as $state) {
            $this->mapState($state);
        }

        if ($this->initial_state === null) {
            throw new Error('No initial state given in the array of states');
        }
    }

    /**
     * Returns the state machine's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the state machine's initial state.
     *
     * @return StateInterface
     */
    public function getInitialState()
    {
        return $this->initial_state;
    }

    /**
     * Returns the state machine's final states.
     *
     * @return array A list of StateInterface instances.
     */
    public function getFinalStates()
    {
        return $this->final_states;
    }

    /**
     * Returns the state machine's event states,
     * hence states that have their transitions connected through events, rather than sequentially.
     *
     * @return array A list of StateInterface instances.
     */
    public function getEventStates()
    {
        return $this->event_states;
    }

    /**
     * Tells whether a given state has event based or sequential transitions.
     *
     * @param mixed $state_or_state_name Either an instance of StateInterface or string.
     *
     * @return bool
     */
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

    /**
     * Executes the state machine against the execution context of the given subject.
     * The state machine will traverse the graph until it reaches an event- or final-state.
     *
     * @param StatefulSubjectInterface $subject
     * @param string $event_name
     *
     * @return StateInterface The state at which the execution suspended or finished.
     */
    public function execute(StatefulSubjectInterface $subject, $event_name = null)
    {
        $event_name = $event_name ?: self::SEQ_TRANSITIONS_KEY;
        $current_state = $this->getValidStartStateFor($subject);

        do {
            $this->leaveState($subject, $current_state);

            $next_state = $this->getNextState($subject, $current_state, $event_name);
            $this->enterState($subject, $next_state);

            $current_state = $next_state;
            // after the initial event has been processed, only sequential transitions will continue execution
            $event_name = self::SEQ_TRANSITIONS_KEY;
            // keep traversing the graph until we reach an event- or final state.
        } while (!$this->isEventState($current_state) && !$current_state->isFinal());

        return $current_state;
    }

    /**
     * Returns all of the state machine's states.
     *
     * @return array A list of StateInterface instances.
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Retrieves a state from the state machine by name.
     *
     * @return StateInterface
     */
    public function getState($state_name)
    {
        $state = null;

        if (isset($this->states[$state_name])) {
            $state = $this->states[$state_name];
        }

        return $state;
    }

    /**
     * Depending on what parameters are set either returns all transitions for a given state
     * or just the state transitions for a particular event.
     *
     * @param string $state_name Only return transitions for the given state.
     * @param string $event_name Only return the state-transitions for the given event.
     *
     * @return array An array of Workflux\Transition\TransitionInterface
     *
     * @throws Error if either a given state and/or event are not supported.
     */
    public function getTransitions($state_name = '', $event_name = '')
    {
        if (!empty($event_name)) {
            return $this->getEventTransitions($state_name, $event_name);
        } elseif (!empty($state_name)) {
            return $this->getStateTransitions($state_name);
        } else {
            return $this->transitions;
        }
    }

    /**
     * Returns an array of transition belonging to the given state-event tuple.
     *
     * @param string $state_name
     * @param string $event_name
     *
     * @return array An array of Workflux\Transition\TransitionInterface.
     *
     * @throws Error if the given state does not exist or doesn't have any transitions set.
     */
    protected function getEventTransitions($state_name, $event_name)
    {
        $state_transitions = $this->getStateTransitions($state_name);

        if (!isset($state_transitions[$event_name])) {
            throw new Error(
                sprintf('No transitions available for event "%s" at state "%s".', $event_name, $state_name)
            );
        }

        return $state_transitions[$event_name];
    }

    /**
     * Returns an array of transition belonging to the given state.
     *
     * @param string $state_name
     *
     * @return array An array of Workflux\Transition\TransitionInterface.
     *
     * @throws Error if the given state does not exist or doesn't have any transitions set.
     */
    protected function getStateTransitions($state_name)
    {
        if (!isset($this->transitions[$state_name])) {
            throw new Error(sprintf('No transitions available at state "%s".', $state_name));
        }

        return $this->transitions[$state_name];
    }

    /**
     * Maps the given state to one of initial_state, event_states or final_states.
     *
     * @param StateInterface $state
     */
    protected function mapState(StateInterface $state)
    {
        switch ($state->getType()) {
            case StateInterface::TYPE_FINAL:
                $this->final_states[] = $state;
                break;
            case StateInterface::TYPE_INITIAL:
                $this->initial_state = $state;
                // no break
            default:
                $state_transitions = $this->getTransitions($state->getName());
                if (!isset($state_transitions[StateMachine::SEQ_TRANSITIONS_KEY])) {
                    $this->event_states[] = $state;
                }
        }
    }

    /**
     * Returns the state to resume the execution at for the given subject.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return StateInterface
     */
    protected function getValidStartStateFor(StatefulSubjectInterface $subject)
    {
        $state_name = $subject->getExecutionContext()->getCurrentStateName();

        if (!$state_name) {
            $start_state = $this->initializeExecutionState($subject);
        } else {
            $start_state = $this->resumeExecutionState($subject);
        }

        if ($start_state->isFinal()) {
            throw new Error(
                sprintf(
                    'Current execution is pointing to a final state "%s".' .
                    ' The state machine execution may not be resumed at a final state.',
                    $start_state->getName()
                )
            );
        }

        return $start_state;
    }

    /**
     * Initializes the execution context of the given subject, hence enters the initial state.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return StateInterface
     */
    protected function initializeExecutionState(StatefulSubjectInterface $subject)
    {
        $start_state = $this->getInitialState();
        $this->enterState($subject, $start_state);

        return $start_state;
    }

    /**
     * Determines the state at which to resume the exexution.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return StateInterface
     *
     * @throws Error If the state exposed by the subject's execution context is invalid or does not exist.
     */
    protected function resumeExecutionState(StatefulSubjectInterface $subject)
    {
        return $this->getStateOrFail(
            $subject->getExecutionContext()->getCurrentStateName()
        );
    }

    /**
     * Return the next state to transition to,
     * while traversing the state machine graph in the context of the given stateful subject.
     *
     * @param StatefulSubjectInterface $subject
     * @param StateInterface $current_state
     * @param string $event_name
     *
     * @throws Error If the subject couldn't transit to the next state.
     *
     * @return StateInterface
     */
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

    /**
     * Determine the correct transition to take while leaving the current state.
     *
     * @param StatefulSubjectInterface $subject
     * @param StateInterface $state
     * @param string $event_name
     *
     * @throws Error In cases where either more than one or no transition at all have accpeted the subject.
     *
     * @return TransitionInterface
     */
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
                    sprintf(
                        'Only one transition is allowed to be active at a time: event=%s state=%s',
                        $event_name,
                        $state->getName()
                    )
                );
            }

            $accepted_transition = $state_transition;
        }

        return $accepted_transition;
    }

    /**
     * Checks if the given subject may proceed trough the given transition.
     *
     * @param StatefulSubjectInterface $subject
     * @param TransitionInterface $transition
     *
     * @return bool
     */
    protected function mayProceed(StatefulSubjectInterface $subject, TransitionInterface $transition)
    {
        if (!$transition->hasGuard()) {
            return true;
        }

        return $transition->getGuard()->accept($subject);
    }

    /**
     * Returns the state for the given name or raises an error.
     *
     * @throws Error If there is not state with the given name.
     *
     * @return StateInterface
     */
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

    /**
     * Executes the onExit handler for the current state before applying a transition.
     *
     * @param StatefulSubjectInterface $subject
     * @param StateInterface $current_state
     */
    protected function leaveState(StatefulSubjectInterface $subject, StateInterface $current_state)
    {
        $current_state->onExit($subject);
    }

    /**
     * Executes the onEnter handler for the current state before applying a transition.
     *
     * @param StatefulSubjectInterface $subject
     * @param StateInterface $next_state
     */
    protected function enterState(StatefulSubjectInterface $subject, StateInterface $next_state)
    {
        $next_state->onEntry($subject);
    }
}
