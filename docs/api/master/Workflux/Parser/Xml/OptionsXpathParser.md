<small>Workflux\Parser\Xml</small>

OptionsXpathParser
==================

The OptionsXpathParser can parse &#039;options&#039; that are defined below a given context node.

Signature
---------

- It is a(n) **class**.
- It implements the [`ParserInterface`](../../../Workflux/Parser/ParserInterface.md) interface.

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new OptionsXpathParser instance that uses the given xpath.
- [`literalize()`](#literalize) &mdash; Takes a xml node value and casts it to it&#039;s php scalar counterpart.
- [`literalizeString()`](#literalizeString) &mdash; Takes an xml node value and returns it either as a string or boolean.
- [`parse()`](#parse) &mdash; Parses all options below the given &#039;options context&#039; in an array.

### `__construct()` <a name="__construct"></a>

Creates a new OptionsXpathParser instance that uses the given xpath.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$xpath` ([`Xpath`](../../../Workflux/Parser/Xml/Xpath.md))
- It does not return anything.

### `literalize()` <a name="literalize"></a>

Takes a xml node value and casts it to it&#039;s php scalar counterpart.

#### Signature

- It is a **public static** method.
- It accepts the following parameter(s):
    - `$value` (`string`)
- _Returns:_ | boolean | int
    - `string`

### `literalizeString()` <a name="literalizeString"></a>

Takes an xml node value and returns it either as a string or boolean.

#### Signature

- It is a **public static** method.
- It accepts the following parameter(s):
    - `$value` (`string`) &mdash; Following values are cast to bool true/false: on, yes, true/off, no, false
- _Returns:_ | boolean
    - `string`

### `parse()` <a name="parse"></a>

Parses all options below the given &#039;options context&#039; in an array.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$options_context` (`mixed`)
- It returns a(n) `mixed` value.

