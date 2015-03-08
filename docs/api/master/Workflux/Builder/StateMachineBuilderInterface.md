<small>Workflux\Builder</small>

StateMachineBuilderInterface
============================

StateMachineBuilderInterface implementations are supposed to provide convenience for building state machines.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`setStateMachineName()`](#setStateMachineName) &mdash; Sets the state machine&#039;s name.
- [`addState()`](#addState) &mdash; Adds the given state to the state machine setup.
- [`addStates()`](#addStates) &mdash; Adds the given states to the state machine setup.
- [`addTransition()`](#addTransition) &mdash; Adds a single transition to the state machine setup for a given event.
- [`addTransitions()`](#addTransitions) &mdash; Convenience method for adding multiple event-transition combinations at once.
- [`build()`](#build) &mdash; Verifies the builder&#039;s current state and builds a state machine off of it.

### `setStateMachineName()` <a name="setStateMachineName"></a>

Sets the state machine&#039;s name.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_machine_name` (`string`)
- It returns a(n) [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) value.

### `addState()` <a name="addState"></a>

Adds the given state to the state machine setup.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state` ([`StateInterface`](../../Workflux/State/StateInterface.md))
- It returns a(n) [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) value.

### `addStates()` <a name="addStates"></a>

Adds the given states to the state machine setup.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$states` (`array`) &mdash; An array of StateInterface instances.
- It returns a(n) [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) value.

### `addTransition()` <a name="addTransition"></a>

Adds a single transition to the state machine setup for a given event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$transition` ([`TransitionInterface`](../../Workflux/Transition/TransitionInterface.md))
    - `$event_name` (`string`) &mdash; If the event name is omitted, then the transition will act as sequential.
- It returns a(n) [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) value.

### `addTransitions()` <a name="addTransitions"></a>

Convenience method for adding multiple event-transition combinations at once.

#### Description

This method does not work for adding sequential transitions, because they don&#039;t have an event.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$event_transitions` (`array`)
- It returns a(n) [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) value.

### `build()` <a name="build"></a>

Verifies the builder&#039;s current state and builds a state machine off of it.

#### Signature

- It is a **public** method.
- It returns a(n) [`StateMachineInterface`](../../Workflux/StateMachine/StateMachineInterface.md) value.

