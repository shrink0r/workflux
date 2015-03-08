<small>Workflux</small>

ExecutionContext
================

Standard implementation of the ExecutionContextInterface.

Signature
---------

- It is a(n) **class**.
- It implements the [`ExecutionContextInterface`](../Workflux/ExecutionContextInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new ExecutionContext instance.
- [`getStateMachineName()`](#getStateMachineName) &mdash; Returns the name of the state machine, where the execution shall start/resume.
- [`getCurrentStateName()`](#getCurrentStateName) &mdash; Returns the name of the state machine state, where the execution shall resume.
- [`onStateEntry()`](#onStateEntry) &mdash; Sets the current state name.
- [`onStateExit()`](#onStateExit) &mdash; Is called when the state machine exits it&#039;s state.

### `__construct()` <a name="__construct"></a>

Creates a new ExecutionContext instance.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_machine_name`
    - `$current_state_name`
    - `$parameters` (`array`)
- It does not return anything.

### `getStateMachineName()` <a name="getStateMachineName"></a>

Returns the name of the state machine, where the execution shall start/resume.

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

### `getCurrentStateName()` <a name="getCurrentStateName"></a>

Returns the name of the state machine state, where the execution shall resume.

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

### `onStateEntry()` <a name="onStateEntry"></a>

Sets the current state name.

#### Description

Is called when the state machine enters a new state

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state` ([`StateInterface`](../Workflux/State/StateInterface.md))
- It does not return anything.

### `onStateExit()` <a name="onStateExit"></a>

Is called when the state machine exits it&#039;s state.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state` ([`StateInterface`](../Workflux/State/StateInterface.md))
- It does not return anything.

