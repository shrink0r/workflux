<small>Workflux\Guard</small>

ExpressionGuard
===============

The ExpressionGuard employs it&#039;s verfification based on the evaluation of a given (symfony) expression.

Description
-----------

The following variables are available within the configured expression:
&quot;subject&quot; - The StatefulSubjectInterface that is being accepted/rejected.
&quot;params&quot;  - The parameters array of the subject&#039;s execution context.

Signature
---------

- It is a(n) **class**.
- It is a subclass of [`ConfigurableGuard`](../../Workflux/Guard/ConfigurableGuard.md).

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new ExpressionGuard instance based on with the given options.
- [`accept()`](#accept) &mdash; Evaluates the configured (symfony) expression for the given subject.
- [`__toString()`](#__toString) &mdash; Returns a string represenation of the guard.

### `__construct()` <a name="__construct"></a>

Creates a new ExpressionGuard instance based on with the given options.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$options` (`array`)
- It does not return anything.

### `accept()` <a name="accept"></a>

Evaluates the configured (symfony) expression for the given subject.

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

