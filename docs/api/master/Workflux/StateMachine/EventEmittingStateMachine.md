<small>Workflux\StateMachine</small>

EventEmittingStateMachine
=========================

Adds events to the default StateMachine implementation.

Signature
---------

- It is a(n) **class**.
- It implements the `Evenement\EventEmitterInterface` interface.
- It is a subclass of [`StateMachine`](../../Workflux/StateMachine/StateMachine.md).

Constants
---------

This class defines the following constants:

- [`ON_EXECUTION_STARTED`](#ON_EXECUTION_STARTED)
- [`ON_EXECUTION_SUSPENDED`](#ON_EXECUTION_SUSPENDED)
- [`ON_EXECUTION_RESUMED`](#ON_EXECUTION_RESUMED)
- [`ON_EXECUTION_FINISHED`](#ON_EXECUTION_FINISHED)
- [`ON_STATE_ENTERED`](#ON_STATE_ENTERED)
- [`ON_STATE_EXITED`](#ON_STATE_EXITED)

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new EventEmittingStateMachine instance, by either using a given event emitter or otherwise creating it&#039;s own.
- [`execute()`](#execute) &mdash; Overrides the &quot;StateMachine::execute&quot; method in order to support/emit the ON_EXECUTION_SUSPENDED and ON_EXECUTION_FINISHED events.
- [`on()`](#on) &mdash; Registers the given listener for the given event.
- [`once()`](#once) &mdash; Registers the given listener to respond only once to the given event.
- [`removeListener()`](#removeListener) &mdash; Removes the given listener for the given event.
- [`removeAllListeners()`](#removeAllListeners) &mdash; Removes all listeners from all events or just the listeners for a given event.
- [`listeners()`](#listeners) &mdash; Returns all the listeners for a specific event.
- [`emit()`](#emit) &mdash; Emits the given event to all registered listeners.

### `__construct()` <a name="__construct"></a>

Creates a new EventEmittingStateMachine instance, by either using a given event emitter or otherwise creating it&#039;s own.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$name` (`string`)
    - `$states` (`array`)
    - `$transitions` (`array`)
    - `$event_emitter` (`Evenement\EventEmitterInterface`)
- It does not return anything.

### `execute()` <a name="execute"></a>

Overrides the &quot;StateMachine::execute&quot; method in order to support/emit the ON_EXECUTION_SUSPENDED and ON_EXECUTION_FINISHED events.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
    - `$transition_event`
- _Returns:_ The state at which the execution was suspended or finished.
    - [`StateInterface`](../../Workflux/State/StateInterface.md)

### `on()` <a name="on"></a>

Registers the given listener for the given event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$event` (`string`) &mdash; Must be one of state machine&#039;s $supported_events, hence one of the ON_* constants.
    - `$listener` (`callable`)
- It does not return anything.
- It throws one of the following exceptions:
    - `Error` &mdash; If the given event is not supported.

### `once()` <a name="once"></a>

Registers the given listener to respond only once to the given event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$event` (`string`) &mdash; Must be one of state machine&#039;s $supported_events, hence one of the ON_* constants.
    - `$listener` (`callable`)
- It does not return anything.
- It throws one of the following exceptions:
    - `Error` &mdash; If the given event is not supported.

### `removeListener()` <a name="removeListener"></a>

Removes the given listener for the given event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$event` (`string`) &mdash; Must be one of state machine&#039;s $supported_events, hence one of the ON_* constants.
    - `$listener` (`callable`)
- It does not return anything.

### `removeAllListeners()` <a name="removeAllListeners"></a>

Removes all listeners from all events or just the listeners for a given event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$event`
- It does not return anything.

### `listeners()` <a name="listeners"></a>

Returns all the listeners for a specific event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$event` (`string`)
- It does not return anything.

### `emit()` <a name="emit"></a>

Emits the given event to all registered listeners.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$event` (`string`)
    - `$arguments` (`array`)
- It does not return anything.
- It throws one of the following exceptions:
    - `Error` &mdash; If the given event is not supported.

