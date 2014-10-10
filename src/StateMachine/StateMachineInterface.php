<?php

namespace Workflux\StateMachine;

use Workflux\StatefulSubjectInterface;
use Workflux\State\StateInterface;

/**
 * StateMachineInterface implementations are expected to act as event triggered finite state machines.
 * More api doc tbd...
 */
interface StateMachineInterface
{
    /**
     * Returns the state machine's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Executes the state machine against the execution context of the given subject.
     * The state machine will traverse the graph until it reaches an event- or final-state.
     *
     * @param StatefulSubjectInterface $subject
     * @param string $event_name
     *
     * @return StateInterface The state at which the execution was suspended.
     */
    public function execute(StatefulSubjectInterface $subject, $event_name);

    /**
     * Returns the state machine's initial state.
     *
     * @return StateInterface
     */
    public function getInitialState();

    /**
     * Returns the state machine's final states.
     *
     * @return array A list of StateInterface instances.
     */
    public function getFinalStates();

    /**
     * Returns the state machine's event states,
     * hence states that have their transitions connected through events, rather than sequentially.
     *
     * @return array A list of StateInterface instances.
     */
    public function getEventStates();

    /**
     * Returns all of the state machine's states.
     *
     * @return array A list of StateInterface instances.
     */
    public function getStates();

    /**
     * Retrieves a state from the state machine by name.
     *
     * @return StateInterface
     */
    public function getState($state_name);

    /**
     * Tells whether a given state has event based or sequential transitions.
     *
     * @param string $state_name
     *
     * @return bool
     */
    public function isEventState($state_name);

    /**
     * Depending on what parameters are set either all transitions are returned or a filtered subset.
     *
     * @param string $state_name Only return transitions for the given state.
     * @param string $event_name Only return the state-transitions for the given event.
     *
     * @return array
     */
    public function getTransitions($state_name = '', $event_name = '');
}
