<small>Workflux\Builder</small>

StateMachineBuilder
===================

The StateMachineBuilder provides a fluent api for defining state machines.

Description
-----------

The builder verifies the setup before creating the state machine,
which makes it easier to spot errors when building automata.

Signature
---------

- It is a(n) **class**.
- It implements the [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct)
- [`setStateMachineName()`](#setStateMachineName) &mdash; Sets the state machine&#039;s name.
- [`setStateMachineClass()`](#setStateMachineClass) &mdash; Sets the state machine&#039;s class/implementor.
- [`addState()`](#addState) &mdash; Adds the given state to the state machine setup.
- [`addStates()`](#addStates) &mdash; Adds the given states to the state machine setup.
- [`addTransition()`](#addTransition) &mdash; Adds a single transition to the state machine setup for a given event.
- [`addTransitions()`](#addTransitions) &mdash; Convenience method for adding multiple event-transition combinations at once.
- [`build()`](#build) &mdash; Verifies the builder&#039;s current state and builds a state machine off of it.

### `__construct()` <a name="__construct"></a>

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$options` (`array`)
- It does not return anything.

### `setStateMachineName()` <a name="setStateMachineName"></a>

Sets the state machine&#039;s name.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_machine_name` (`string`)
- It returns a(n) [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) value.

### `setStateMachineClass()` <a name="setStateMachineClass"></a>

Sets the state machine&#039;s class/implementor.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_machine_class` (`string`)
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
    - `$event_transitions` (`array`) &mdash; The array is expected too be structured as followed by example: &lt;code&gt; [ $event_name =&gt; [ $transition1, $transition1 ], $other_event_name =&gt; $other_transition, // you can add either add an array of transitions or just one ... ] &lt;/code&gt;
- It returns a(n) [`StateMachineBuilderInterface`](../../Workflux/Builder/StateMachineBuilderInterface.md) value.

### `build()` <a name="build"></a>

Verifies the builder&#039;s current state and builds a state machine off of it.

#### Signature

- It is a **public** method.
- It returns a(n) [`StateMachineInterface`](../../Workflux/StateMachine/StateMachineInterface.md) value.

