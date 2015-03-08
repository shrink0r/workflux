<small>Workflux\StateMachine</small>

StateMachine
============

General default implementation of the StateMachineInterface.

Signature
---------

- It is a(n) **class**.
- It implements the [`StateMachineInterface`](../../Workflux/StateMachine/StateMachineInterface.md) interface.

Constants
---------

This class defines the following constants:

- [`SEQ_TRANSITIONS_KEY`](#SEQ_TRANSITIONS_KEY)

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new StateMachine instance.
- [`getName()`](#getName) &mdash; Returns the state machine&#039;s name.
- [`getInitialState()`](#getInitialState) &mdash; Returns the state machine&#039;s initial state.
- [`getFinalStates()`](#getFinalStates) &mdash; Returns the state machine&#039;s final states.
- [`getEventStates()`](#getEventStates) &mdash; Returns the state machine&#039;s event states, hence states that have their transitions connected through events, rather than sequentially.
- [`isEventState()`](#isEventState) &mdash; Tells whether a given state has event based or sequential transitions.
- [`execute()`](#execute) &mdash; Executes the state machine against the execution context of the given subject.
- [`getStates()`](#getStates) &mdash; Returns all of the state machine&#039;s states.
- [`getState()`](#getState) &mdash; Retrieves a state from the state machine by name.
- [`getTransitions()`](#getTransitions) &mdash; Depending on what parameters are set either returns all transitions for a given state or just the state transitions for a particular event.

### `__construct()` <a name="__construct"></a>

Creates a new StateMachine instance.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$name` (`string`)
    - `$states` (`array`)
    - `$transitions` (`array`)
- It does not return anything.

### `getName()` <a name="getName"></a>

Returns the state machine&#039;s name.

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

### `getInitialState()` <a name="getInitialState"></a>

Returns the state machine&#039;s initial state.

#### Signature

- It is a **public** method.
- It returns a(n) [`StateInterface`](../../Workflux/State/StateInterface.md) value.

### `getFinalStates()` <a name="getFinalStates"></a>

Returns the state machine&#039;s final states.

#### Signature

- It is a **public** method.
- _Returns:_ A list of StateInterface instances.
    - `array`

### `getEventStates()` <a name="getEventStates"></a>

Returns the state machine&#039;s event states, hence states that have their transitions connected through events, rather than sequentially.

#### Signature

- It is a **public** method.
- _Returns:_ A list of StateInterface instances.
    - `array`

### `isEventState()` <a name="isEventState"></a>

Tells whether a given state has event based or sequential transitions.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_or_state_name`
- It returns a(n) `bool` value.

### `execute()` <a name="execute"></a>

Executes the state machine against the execution context of the given subject.

#### Description

The state machine will traverse the graph until it reaches an event- or final-state.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
    - `$event_name` (`string`)
- _Returns:_ The state at which the execution suspended or finished.
    - [`StateInterface`](../../Workflux/State/StateInterface.md)

### `getStates()` <a name="getStates"></a>

Returns all of the state machine&#039;s states.

#### Signature

- It is a **public** method.
- _Returns:_ A list of StateInterface instances.
    - `array`

### `getState()` <a name="getState"></a>

Retrieves a state from the state machine by name.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_name`
- It returns a(n) [`StateInterface`](../../Workflux/State/StateInterface.md) value.

### `getTransitions()` <a name="getTransitions"></a>

Depending on what parameters are set either returns all transitions for a given state or just the state transitions for a particular event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_name` (`string`) &mdash; Only return transitions for the given state.
    - `$event_name` (`string`) &mdash; Only return the state-transitions for the given event.
- _Returns:_ An array of Workflux\Transition\TransitionInterface
    - `array`
- It throws one of the following exceptions:
    - `Error` &mdash; if either a given state and/or event are not supported.

