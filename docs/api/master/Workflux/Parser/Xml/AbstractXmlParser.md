<small>Workflux\Parser\Xml</small>

AbstractXmlParser
=================

The AbstsractXmlParser serves as base class for xml parsers.

Signature
---------

- It is a(n) **abstract class**.
- It implements the [`ParserInterface`](../../../Workflux/Parser/ParserInterface.md) interface.

Methods
-------

The abstract class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new StateMachineDefinitionParser instance.
- [`parse()`](#parse) &mdash; Parses the given xml file and returns the corresponding data.

### `__construct()` <a name="__construct"></a>

Creates a new StateMachineDefinitionParser instance.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$options` (`array`)
- It does not return anything.

### `parse()` <a name="parse"></a>

Parses the given xml file and returns the corresponding data.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$xml_file` (`string`) &mdash; Vaild filesystem path to a xml file.
- _Returns:_ The parsed data.
    - `mixed`

