<small>Workflux\Guard</small>

CallbackGuard
=============

The CallbackGuard employs it&#039;s verification strategy by simply delegating the verification to a given callback.

Signature
---------

- It is a(n) **class**.
- It implements the [`GuardInterface`](../../Workflux/Guard/GuardInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new CallbackGuard instance.
- [`accept()`](#accept) &mdash; Invokes the configured callback on a given stateful subject and returns the result.
- [`__toString()`](#__toString) &mdash; Returns a string represenation of the guard.

### `__construct()` <a name="__construct"></a>

Creates a new CallbackGuard instance.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$callback` (`callable`)
- It does not return anything.

### `accept()` <a name="accept"></a>

Invokes the configured callback on a given stateful subject and returns the result.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
- It returns a(n) `boolean` value.

### `__toString()` <a name="__toString"></a>

Returns a string represenation of the guard.

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

