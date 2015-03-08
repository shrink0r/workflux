<small>Workflux\Transition</small>

Transition
==========

Standard implementation of the TransitionInterface.

Signature
---------

- It is a(n) **class**.
- It implements the [`TransitionInterface`](../../Workflux/Transition/TransitionInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new Transition instance.
- [`getIncomingStateNames()`](#getIncomingStateNames) &mdash; Returns the names of the transition&#039;s incoming states.
- [`getOutgoingStateName()`](#getOutgoingStateName) &mdash; Returns the name of the transition&#039;s outgoing state.
- [`getGuard()`](#getGuard) &mdash; Returns the transition&#039;s guard.
- [`hasGuard()`](#hasGuard) &mdash; Tells whether the transition has a guard or not.

### `__construct()` <a name="__construct"></a>

Creates a new Transition instance.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$incoming_state_name_or_names` (`mixed`)
    - `$outgoing_state_name` (`string`)
    - `$guard` ([`GuardInterface`](../../Workflux/Guard/GuardInterface.md))
- It does not return anything.

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

