<small>Workflux\Renderer</small>

GraphRendererInterface
======================

GraphRendererInterface implementations are expected to render StateMachineInterface instances into any kind of specific output format, like for example xml, yaml or dot.

Signature
---------

- It is a(n) **interface**.

Methods
-------

The interface defines the following methods:

- [`renderGraph()`](#renderGraph) &mdash; Renders the given state machine to a specific format.

### `renderGraph()` <a name="renderGraph"></a>

Renders the given state machine to a specific format.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_machine` ([`StateMachineInterface`](../../Workflux/StateMachine/StateMachineInterface.md))
- It returns a(n) `mixed` value.

