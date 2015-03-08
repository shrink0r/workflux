<small>Workflux\Guard</small>

VariableGuard
=============

The VariableGuard employs it&#039;s verfification based on the evaluation of a given (symfony) expression.

Description
-----------

It makes all execution context parameters directly addressable within the expression.

Signature
---------

- It is a(n) **class**.
- It is a subclass of [`ExpressionGuard`](../../Workflux/Guard/ExpressionGuard.md).

Methods
-------

The class defines the following methods:

- [`accept()`](#accept) &mdash; Evaluates the configured (symfony) expression for the given subject.

### `accept()` <a name="accept"></a>

Evaluates the configured (symfony) expression for the given subject.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
- It returns a(n) `boolean` value.

