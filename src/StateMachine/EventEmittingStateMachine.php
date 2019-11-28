<?php

namespace Workflux\StateMachine;

use Workflux\Error\Error;
use Workflux\StatefulSubjectInterface;
use Workflux\State\StateInterface;
use Evenement\EventEmitter;
use Evenement\EventEmitterInterface;

/**
 * Adds events to the default StateMachine implementation.
 */
class EventEmittingStateMachine extends StateMachine implements EventEmitterInterface
{
    /**
     * @var string ON_EXECUTION_STARTED
     */
    const ON_EXECUTION_STARTED = 'workflux.state_machine.execution_started';

    /**
     * @var string ON_EXECUTION_SUSPENDED
     */
    const ON_EXECUTION_SUSPENDED = 'workflux.state_machine.execution_suspended';

    /**
     * @var string ON_EXECUTION_RESUMED
     */
    const ON_EXECUTION_RESUMED = 'workflux.state_machine.execution_resumed';

    /**
     * @var string ON_EXECUTION_FINISHED
     */
    const ON_EXECUTION_FINISHED = 'workflux.state_machine.execution_finished';

    /**
     * @var string ON_STATE_ENTERED
     */
    const ON_STATE_ENTERED = 'workflux.state_machine.state_entered';

    /**
     * @var string ON_STATE_EXITED
     */
    const ON_STATE_EXITED = 'workflux.state_machine.state_exited';

    /**
     * @var array $supported_events
     */
    protected static $supported_events = [
        self::ON_EXECUTION_STARTED,
        self::ON_STATE_ENTERED,
        self::ON_STATE_EXITED,
        self::ON_EXECUTION_SUSPENDED,
        self::ON_EXECUTION_RESUMED,
        self::ON_EXECUTION_FINISHED
    ];

    /**
     * @var EventEmitterInterface $event_emitter
     */
    protected $event_emitter;

    /**
     * Creates a new EventEmittingStateMachine instance,
     * by either using a given event emitter or otherwise creating it's own.
     *
     * @param string $name
     * @param array $states
     * @param array $transitions
     * @param EventEmitterInterface $event_emitter
     */
    public function __construct($name, array $states, array $transitions, EventEmitterInterface $event_emitter = null)
    {
        parent::__construct($name, $states, $transitions);

        $this->event_emitter = $event_emitter ?: new EventEmitter();
    }

    /**
     * Overrides the "StateMachine::execute" method
     * in order to support/emit the ON_EXECUTION_SUSPENDED and ON_EXECUTION_FINISHED events.
     *
     * @param StatefulSubjectInterface $subject
     * @param string $transition_event
     *
     * @return StateInterface The state at which the execution was suspended or finished.
     */
    public function execute(StatefulSubjectInterface $subject, $transition_event = null)
    {
        $current_state = parent::execute($subject, $transition_event);

        if ($this->isEventState($current_state)) {
            $this->fireEvent(self::ON_EXECUTION_SUSPENDED, $subject, $current_state);
        } else {
            $this->fireEvent(self::ON_EXECUTION_FINISHED, $subject, $current_state);
        }

        return $current_state;
    }

    /**
     * Registers the given listener for the given event.
     *
     * @param string $event Must be one of state machine's $supported_events, hence one of the ON_* constants.
     * @param callable $listener
     *
     * @throws Error If the given event is not supported.
     */
    public function on($event, callable $listener)
    {
        $this->guardSupportedEvents($event);

        $this->event_emitter->on($event, $listener);
    }

    /**
     * Registers the given listener to respond only once to the given event.
     *
     * @param string $event Must be one of state machine's $supported_events, hence one of the ON_* constants.
     * @param callable $listener
     *
     * @throws Error If the given event is not supported.
     */
    public function once($event, callable $listener)
    {
        $this->guardSupportedEvents($event);

        $this->event_emitter->once($event, $listener);
    }

    /**
     * Removes the given listener for the given event.
     *
     * @param string $event Must be one of state machine's $supported_events, hence one of the ON_* constants.
     * @param callable $listener
     */
    public function removeListener($event, callable $listener)
    {
        $this->event_emitter->removeListener($event, $listener);
    }

    /**
     * Removes all listeners from all events or just the listeners for a given event.
     *
     * @param string $event If not given all listeners will removed from all events.
     */
    public function removeAllListeners($event = null)
    {
        $this->event_emitter->removeAllListeners($event);
    }

    /**
     * Returns all the listeners for a specific event.
     *
     * @param string|null $event
     */
    public function listeners($event = null)
    {
        return $this->event_emitter->listeners($event);
    }

    /**
     * Emits the given event to all registered listeners.
     *
     * @param string $event
     * @param array $arguments
     *
     * @throws Error If the given event is not supported.
     */
    public function emit($event, array $arguments = [])
    {
        $this->guardSupportedEvents($event);

        $this->event_emitter->emit($event, $arguments);
    }

    /**
     * Overrides the "StateMachine::initializeExecutionState" method,
     * in order to support/emit the ON_EXECUTION_STARTED event.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return StateInterface
     */
    protected function initializeExecutionState(StatefulSubjectInterface $subject)
    {
        $initial_state = parent::initializeExecutionState($subject);

        $this->fireEvent(self::ON_EXECUTION_STARTED, $subject, $initial_state);

        return $initial_state;
    }

    /**
     * Overrides the "StateMachine::resumeExecutionState" method,
     * in order to support/emit the ON_EXECUTION_RESUMED event.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return StateInterface
     */
    protected function resumeExecutionState(StatefulSubjectInterface $subject)
    {
        $resume_state = parent::resumeExecutionState($subject);

        $this->fireEvent(self::ON_EXECUTION_RESUMED, $subject, $resume_state);

        return $resume_state;
    }

    /**
     * Makes sure the given event has support for being emitted by the state machine.
     *
     * @param string $event
     *
     * @throws Error If the given event is not supported.
     */
    protected function guardSupportedEvents($event)
    {
        if (!in_array($event, self::$supported_events)) {
            throw new Error(
                sprintf(
                    'Trying to register non supported event "%s". Supported are: %s',
                    $event,
                    implode(', ', self::$supported_events)
                )
            );
        }
    }

    /**
     * Overrides the "StateMachine::leaveState" method,
     * in order to support/emit the ON_STATE_EXITED event.
     *
     * @param StatefulSubjectInterface $subject
     * @param StateInterface $current_state
     */
    protected function leaveState(StatefulSubjectInterface $subject, StateInterface $current_state)
    {
        parent::leaveState($subject, $current_state);

        $this->fireEvent(self::ON_STATE_EXITED, $subject, $current_state);
    }

    /**
     * Overrides the "StateMachine::enterState" method,
     * in order to support/emit the ON_STATE_ENTERED event.
     *
     * @param StatefulSubjectInterface $subject
     * @param StateInterface $next_state
     */
    protected function enterState(StatefulSubjectInterface $subject, StateInterface $next_state)
    {
        parent::enterState($subject, $next_state);

        $this->fireEvent(self::ON_STATE_ENTERED, $subject, $next_state);
    }

    /**
     * Emits the given state machine event,
     * passing the given subject, affected state and the state machine itself as arguments to anyone listening.
     *
     * @param string $event Must be one of state machine's $supported_events, hence one of the ON_* constants.
     * @param StatefulSubjectInterface $subject
     * @param StateInterface $affected_state
     *
     * @throws Error If the given event is not supported.
     */
    protected function fireEvent($event, StatefulSubjectInterface $subject, StateInterface $affected_state)
    {
        $this->guardSupportedEvents($event);

        $this->event_emitter->emit($event, [ $this, $subject, $affected_state ]);
    }
}
