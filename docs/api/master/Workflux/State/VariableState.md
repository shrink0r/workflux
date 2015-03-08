<small>Workflux\State</small>

VariableState
=============

The VariableState class set and removes variables when it transition callbacks (onEntry, onExit) are invoked.

Signature
---------

- It is a(n) **class**.
- It is a subclass of [`State`](../../Workflux/State/State.md).

Constants
---------

This class defines the following constants:

- [`OPTION_VARS`](#OPTION_VARS)
- [`OPTION_REMOVE_VARS`](#OPTION_REMOVE_VARS)

Methods
-------

The class defines the following methods:

- [`onEntry()`](#onEntry) &mdash; Propagates the new state machine position to the execution context of the given subject, by calling the execution context&#039;s &quot;onStateEntry&quot; method.
- [`onExit()`](#onExit) &mdash; Propagates the new state machine position to the execution context of the given subject, by calling the execution context&#039;s &quot;onStateExit&quot; method.

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

