<small>Workflux\Builder</small>

VerificationInterface
=====================

VerificationInterface implementations are supposed to execute a specific verfication strategy in the context of assuring state machine validity.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`verify()`](#verify) &mdash; Performs a specific verfication.

### `verify()` <a name="verify"></a>

Performs a specific verfication.

#### Signature

- It is a **public** method.
- It does not return anything.
- It throws one of the following exceptions:
    - [`Workflux\Error\VerificationError`](../../Workflux/Error/VerificationError.md)

