<small>Workflux\Builder</small>

TransitionsVerification
=======================

The TransitionsVerification is responsable for making sure that a given transition configuration is valid.

Signature
---------

- It is a(n) **class**.
- It implements the [`VerificationInterface`](../../Workflux/Builder/VerificationInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct)
- [`verify()`](#verify) &mdash; Verifies that the defined transitions correctly connect all states.

### `__construct()` <a name="__construct"></a>

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$states` (`array`)
    - `$transitions` (`array`)
- It does not return anything.

### `verify()` <a name="verify"></a>

Verifies that the defined transitions correctly connect all states.

#### Signature

- It is a **public** method.
- It does not return anything.
- It throws one of the following exceptions:
    - `VerificationError`

