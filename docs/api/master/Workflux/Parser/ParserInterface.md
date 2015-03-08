<small>Workflux\Parser</small>

ParserInterface
===============

ParserInterface implementations are supposed to parse specific payload and turn it into a common array structure, that is expected by the StateMachineBuilderInterface.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`parse()`](#parse) &mdash; Parses the given payload and returns the corresponding data as an array.

### `parse()` <a name="parse"></a>

Parses the given payload and returns the corresponding data as an array.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$payload` (`mixed`)
- It returns a(n) `array` value.

