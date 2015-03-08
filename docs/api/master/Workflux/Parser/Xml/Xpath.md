<small>Workflux\Parser\Xml</small>

Xpath
=====

The Xpath class is a conveniece wrapper around DOMXpath and simple adds a namespace prefix to queries.

Signature
---------

- It is a(n) **class**.
- It is a subclass of [`DOMXpath`](http://php.net/class.DOMXpath).

Methods
-------

The class defines the following methods:

- [`__construct()`](#__construct) &mdash; Creates a new xpath instance that will use the given &#039;namespace_prefix&#039; when querying the given document.
- [`query()`](#query) &mdash; Takes an xpath expression and preprends the parser&#039;s namespace prefix to each xpath segment.

### `__construct()` <a name="__construct"></a>

Creates a new xpath instance that will use the given &#039;namespace_prefix&#039; when querying the given document.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$document` ([`DOMDocument`](http://php.net/class.DOMDocument))
    - `$namespace_prefix` (`string`)
- It does not return anything.

### `query()` <a name="query"></a>

Takes an xpath expression and preprends the parser&#039;s namespace prefix to each xpath segment.

#### Description

Then it runs the namespaced expression and returns the result.
Example: &#039;//state_machines/state_machine&#039; - expands to -&gt; &#039;//wf:state_machines/wf:state_machine&#039;

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$expression`
    - `$context` ([`DOMNode`](http://php.net/class.DOMNode))
    - `$register_ns`
- It returns a(n) `Workflux\Parser\Xml\DOMNodeList` value.

