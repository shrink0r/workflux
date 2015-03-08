<small>Workflux</small>

ExecutionContextInterface
=========================

ExecutionContextInterface reflects the current state of execution of a subject inside a state machine.

Description
-----------

The StateMachineInterface relies on this interface to determine
where to continue with a suspended state machine execution.
It is also allows to share state machine variables across states in order to control flow.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`getStateMachineName()`](#getStateMachineName) &mdash; Returns the name of the state machine, where the execution shall start/resume.
- [`getCurrentStateName()`](#getCurrentStateName) &mdash; Returns the name of the state machine state, where the execution shall resume.
- [`hasParameter()`](#hasParameter) &mdash; Tells if the context has a specific parameter.
- [`getParameter()`](#getParameter) &mdash; Returns the value for the given parameter key.
- [`getParameters()`](#getParameters) &mdash; Returns execution context&#039;s parameters.
- [`setParameter()`](#setParameter) &mdash; Sets the given parameter.
- [`removeParameter()`](#removeParameter) &mdash; Removes a parameter given by key.
- [`clearParameters()`](#clearParameters) &mdash; Clears all parameters.
- [`setParameters()`](#setParameters) &mdash; Sets the given parameters.
- [`onStateEntry()`](#onStateEntry) &mdash; Is called when the state machine enters a new state.
- [`onStateExit()`](#onStateExit) &mdash; Is called when the state machine exits it&#039;s state.

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

### `hasParameter()` <a name="hasParameter"></a>

Tells if the context has a specific parameter.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$key` (`string`)
- It returns a(n) `bool` value.

### `getParameter()` <a name="getParameter"></a>

Returns the value for the given parameter key.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$key` (`string`)
    - `$default` (`mixed`)
- _Returns:_ Returns either the parameter value or the given default, if the parameter isn&#039;t set.
    - `mixed`

### `getParameters()` <a name="getParameters"></a>

Returns execution context&#039;s parameters.

#### Signature

- It is a **public** method.
- _Returns:_ Returns either the parameter value or the given default, if the parameter isn&#039;t set.
    - `mixed`

### `setParameter()` <a name="setParameter"></a>

Sets the given parameter.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$key` (`string`)
    - `$value` (`mixed`)
    - `$replace` (`bool`)
- It does not return anything.

### `removeParameter()` <a name="removeParameter"></a>

Removes a parameter given by key.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$key` (`string`)
- It does not return anything.

### `clearParameters()` <a name="clearParameters"></a>

Clears all parameters.

#### Signature

- It is a **public** method.
- It does not return anything.

### `setParameters()` <a name="setParameters"></a>

Sets the given parameters.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$parameters` (`array`)
- It does not return anything.

### `onStateEntry()` <a name="onStateEntry"></a>

Is called when the state machine enters a new state.

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

