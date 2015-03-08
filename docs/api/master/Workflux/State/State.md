<small>Workflux\State</small>

State
=====

The State class is a standard implementation of the StateInterface.

Signature
---------

- It is a(n) **class**.
- It implements the [`StateInterface`](../../Workflux/State/StateInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new State instance.
- [`getName()`](#getName) &mdash; Returns the state&#039;s name.
- [`getType()`](#getType) &mdash; Returns the state&#039;s type.
- [`isInitial()`](#isInitial) &mdash; Tells if a the state is the initial state of the state machine it belongs to.
- [`isActive()`](#isActive) &mdash; Tells if a the state is a active state of the state machine it belongs to.
- [`isFinal()`](#isFinal) &mdash; Tells if a the state is a final state of the state machine it belongs to.
- [`onEntry()`](#onEntry) &mdash; Propagates the new state machine position to the execution context of the given subject, by calling the execution context&#039;s &quot;onStateEntry&quot; method.
- [`onExit()`](#onExit) &mdash; Propagates the new state machine position to the execution context of the given subject, by calling the execution context&#039;s &quot;onStateExit&quot; method.

### `__construct()` <a name="__construct"></a>

Creates a new State instance.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$name` (`string`)
    - `$type` (`string`)
    - `$options` (`array`)
- It does not return anything.

### `getName()` <a name="getName"></a>

Returns the state&#039;s name.

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

### `getType()` <a name="getType"></a>

Returns the state&#039;s type.

#### Signature

- It is a **public** method.
- _Returns:_ One the StateInterface::TYPE_* constant values.
    - `string`

### `isInitial()` <a name="isInitial"></a>

Tells if a the state is the initial state of the state machine it belongs to.

#### Signature

- It is a **public** method.
- It returns a(n) `bool` value.

### `isActive()` <a name="isActive"></a>

Tells if a the state is a active state of the state machine it belongs to.

#### Signature

- It is a **public** method.
- It returns a(n) `bool` value.

### `isFinal()` <a name="isFinal"></a>

Tells if a the state is a final state of the state machine it belongs to.

#### Signature

- It is a **public** method.
- It returns a(n) `bool` value.

### `onEntry()` <a name="onEntry"></a>

Propagates the new state machine position to the execution context of the given subject, by calling the execution context&#039;s &quot;onStateEntry&quot; method.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
- It does not return anything.

### `onExit()` <a name="onExit"></a>

Propagates the new state machine position to the execution context of the given subject, by calling the execution context&#039;s &quot;onStateExit&quot; method.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
- It does not return anything.

