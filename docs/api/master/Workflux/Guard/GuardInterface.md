<small>Workflux\Guard</small>

GuardInterface
==============

GuardInterface implementations are supposed to check, if a given subject is acceptable in the context of transitioning from one state to another.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`accept()`](#accept) &mdash; Tells if a given stateful subject is acceptable and may transit.
- [`__toString()`](#__toString)

### `accept()` <a name="accept"></a>

Tells if a given stateful subject is acceptable and may transit.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$subject` ([`StatefulSubjectInterface`](../../Workflux/StatefulSubjectInterface.md))
- It returns a(n) `boolean` value.

### `__toString()` <a name="__toString"></a>

#### Signature

- It is a **public** method.
- It returns a(n) `string` value.

