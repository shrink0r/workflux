<?php

namespace Workflux\Builder;

use Workflux\State\StateInterface;
use Workflux\Transition\TransitionInterface;
use Workflux\StateMachine\StateMachineInterface;

/**
 * StateMachineBuilderInterface implementations are supposed to provide convenience for building state machines.
 */
interface StateMachineBuilderInterface
{
    /**
     * Sets the state machine's name.
     *
     * @param string $state_machine_name
     *
     * @return StateMachineBuilderInterface
     */
    public function setStateMachineName($state_machine_name);

    /**
     * Adds the given state to the state machine setup.
     *
     * @param StateInterface $state
     *
     * @return StateMachineBuilderInterface
     */
    public function addState(StateInterface $state);

    /**
     * Adds the given states to the state machine setup.
     *
     * @param array $states An array of StateInterface instances.
     *
     * @return StateMachineBuilderInterface
     */
    public function addStates(array $states);

    /**
     * Adds a single transition to the state machine setup for a given event.
     *
     * @param TransitionInterface $transition
     * @param string $event_name If the event name is omitted, then the transition will act as sequential.
     *
     * @return StateMachineBuilderInterface
     */
    public function addTransition(TransitionInterface $transition, $event_name = '');

    /**
     * Convenience method for adding multiple event-transition combinations at once.
     * This method does not work for adding sequential transitions, because they don't have an event.
     *
     * @param array $event_transitions
     *
     * @return StateMachineBuilderInterface
     */
    public function addTransitions(array $event_transitions);

    /**
     * Verifies the builder's current state and builds a state machine off of it.
     *
     * @return StateMachineInterface
     */
    public function build();
}
