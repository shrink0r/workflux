<small>Workflux\StateMachine</small>

StateMachineInterface
=====================

StateMachineInterface implementations are expected to act as event triggered finite state machines.

Description
-----------

More api doc tbd...

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`getName()`](#getName) &mdash; Returns the state machine&#039;s name.
- [`execute()`](#execute) &mdash; Executes the state machine against the execution context of the given subject.
- [`getInitialState()`](#getInitialState) &mdash; Returns the state machine&#039;s initial state.
- [`getFinalStates()`](#getFinalStates) &mdash; Returns the state machine&#039;s final states.
- [`getEventStates()`](#getEventStates) &mdash; Returns the state machine&#039;s event states, hence states that have their transitions connected through events, rather than sequentially.
- [`getStates()`](#getStates) &mdash; Returns all of the state machine&#039;s states.
- [`getState()`](#getState) &mdash; Retrieves a state from the state machine by name.
- [`isEventState()`](#isEventState) &mdash; Tells whether a given state has event based or sequential transitions.
- [`getTransitions()`](#getTransitions) &mdash; Depending on what parameters are set either all transitions are returned or a filtered subset.

### `getName()` <a name="getName"></a>

Returns the state machine&#039;s name.

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

### `execute()` <a name="execute"></a>

Executes the state machine against the execution context of the given subject.

#### Description

The state machine will traverse the graph until it reaches an event- or final-state.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
    - `$event_name` (`string`)
- _Returns:_ The state at which the execution was suspended.
    - [`StateInterface`](../../Workflux/State/StateInterface.md)

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

### `isEventState()` <a name="isEventState"></a>

Tells whether a given state has event based or sequential transitions.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_name` (`mixed`)
- It returns a(n) `bool` value.

### `getTransitions()` <a name="getTransitions"></a>

Depending on what parameters are set either all transitions are returned or a filtered subset.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_name` (`string`) &mdash; Only return transitions for the given state.
    - `$event_name` (`string`) &mdash; Only return the state-transitions for the given event.
- It returns a(n) `array` value.

