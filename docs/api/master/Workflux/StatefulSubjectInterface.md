<small>Workflux</small>

StatefulSubjectInterface
========================

StatefulSubjectInterface provides the main contract between any external objects and the workflux statemachine.

Description
-----------

The subject is always passed to the traversal callbacks
and it&#039;s execution context represents the current traversal state.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`getExecutionContext()`](#getExecutionContext) &mdash; Returns the subject&#039;s execution context.

### `getExecutionContext()` <a name="getExecutionContext"></a>

Returns the subject&#039;s execution context.

#### Signature

- It is a **public** method.
- It returns a(n) [`ExecutionContextInterface`](../Workflux/ExecutionContextInterface.md) value.

