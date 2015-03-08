<small>Workflux\Transition</small>

TransitionInterface
===================

TransitionInterface implementations model the connections between states.

Description
-----------

They define the possible paths of traversal and can guard themselves from being used without permission,
by rejecting access for undesired subjects.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`getIncomingStateNames()`](#getIncomingStateNames) &mdash; Returns the names of the transition&#039;s incoming states.
- [`getOutgoingStateName()`](#getOutgoingStateName) &mdash; Returns the name of the transition&#039;s outgoing state.
- [`getGuard()`](#getGuard) &mdash; Returns the transition&#039;s guard.
- [`hasGuard()`](#hasGuard) &mdash; Tells whether the transition has a guard or not.

### `getIncomingStateNames()` <a name="getIncomingStateNames"></a>

Returns the names of the transition&#039;s incoming states.

#### Signature

- It is a **public** method.
- It returns a(n) `array` value.

### `getOutgoingStateName()` <a name="getOutgoingStateName"></a>

Returns the name of the transition&#039;s outgoing state.

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

### `getGuard()` <a name="getGuard"></a>

Returns the transition&#039;s guard.

#### Signature

- It is a **public** method.
- It returns a(n) [`GuardInterface`](../../Workflux/Guard/GuardInterface.md) value.

### `hasGuard()` <a name="hasGuard"></a>

Tells whether the transition has a guard or not.

#### Signature

- It is a **public** method.
- It returns a(n) `bool` value.

