<?php

namespace Workflux\StateMachine;

use Workflux\Error\Error;
use Workflux\StatefulSubjectInterface;
use Workflux\State\StateInterface;
use Workflux\Transition\TransitionInterface;

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

        $this->initial_state = null;
        $this->final_states = [];
        $this->event_states = [];

        foreach ($this->states as $state) {
            $this->mapState($state);
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
     * @param string $state_name
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
     * @return StateInterface The state at which the execution was suspended.
     */
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
     * Depending on what parameters are set either all transitions are returned or a filtered subset.
     *
     * @param string $state_name Only return transitions for the given state.
     * @param string $event_name Only return the state-transitions for the given event.
     *
     * @return array
     */
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
     * @param StateInterface $current_state
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
                    sprintf('Only one transition is allowed to be active at a time.', $event_name, $state->getName())
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
}
