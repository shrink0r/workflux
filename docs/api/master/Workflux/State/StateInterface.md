<small>Workflux\State</small>

StateInterface
==============

StateInterface implementations are expected to act as a nodes that are part of a state graph.

Description
-----------

The most important methods that allow to expose specific behaviour are &quot;onEntry&quot; and &quot;onExit&quot;.
Typically a state will manipulate the execution context of a given stateful subject in order to express intent.

Signature
---------

- It is a(n) **interface**.

Constants
---------

This interface defines the following constants:

- [`TYPE_INITIAL`](#TYPE_INITIAL)
- [`TYPE_ACTIVE`](#TYPE_ACTIVE)
- [`TYPE_FINAL`](#TYPE_FINAL)

Methods
-------

The interface defines the following methods:

- [`getName()`](#getName) &mdash; Returns the state&#039;s name.
- [`getType()`](#getType) &mdash; Returns the state&#039;s type.
- [`isInitial()`](#isInitial) &mdash; Tells if a the state is the initial state of the state machine it belongs to.
- [`isActive()`](#isActive) &mdash; Tells if a the state is a active state of the state machine it belongs to.
- [`isFinal()`](#isFinal) &mdash; Tells if a the state is an final state of the state machine it belongs to.
- [`onEntry()`](#onEntry) &mdash; Runs a specific action when the parent state machine enters this state.
- [`onExit()`](#onExit) &mdash; Runs a specific action when the parent state machine exits this state.

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

Tells if a the state is an final state of the state machine it belongs to.

#### Signature

- It is a **public** method.
- It returns a(n) `bool` value.

### `onEntry()` <a name="onEntry"></a>

Runs a specific action when the parent state machine enters this state.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
- It does not return anything.

### `onExit()` <a name="onExit"></a>

Runs a specific action when the parent state machine exits this state.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
- It does not return anything.

