<small>Workflux\Builder</small>

StatesVerification
==================

The StatesVerification is responsable for verifying the states configuration of a state machine setup.

Signature
---------

- It is a(n) **class**.
- It implements the [`VerificationInterface`](../../Workflux/Builder/VerificationInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct)
- [`verify()`](#verify) &mdash; Verifies the given states configuration.

### `__construct()` <a name="__construct"></a>

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$states` (`array`)
    - `$transitions` (`array`)
- It does not return anything.

### `verify()` <a name="verify"></a>

Verifies the given states configuration.

#### Signature

- It is a **public** method.
- It does not return anything.
- It throws one of the following exceptions:
    - `VerificationError`

