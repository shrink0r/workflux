<small>Workflux\Renderer</small>

DotGraphRenderer
================

The DotGraphRenderer can render state machines as dot-graphs.

Description
-----------

It supports various options for changing the colors, shapes etc. that are used to style the graph.

Signature
---------

- It is a(n) **class**.
- It is a subclass of [`AbstractRenderer`](../../Workflux/Renderer/AbstractRenderer.md).

Constants
---------

This class defines the following constants:

- [`DOT_TEMPLATE`](#DOT_TEMPLATE)
- [`STATE_NODE_COLOR`](#STATE_NODE_COLOR)
- [`STATE_NODE_FONTCOLOR`](#STATE_NODE_FONTCOLOR)
- [`EDGE_FONTCOLOR`](#EDGE_FONTCOLOR)
- [`EDGE_PROMOTE_COLOR`](#EDGE_PROMOTE_COLOR)
- [`EDGE_DEMOTE_COLOR`](#EDGE_DEMOTE_COLOR)
- [`EDGE_DEFAULT_COLOR`](#EDGE_DEFAULT_COLOR)

Methods
-------

The class defines the following methods:

- [`renderGraph()`](#renderGraph) &mdash; Renders the given state machine as a dot-graph.

### `renderGraph()` <a name="renderGraph"></a>

Renders the given state machine as a dot-graph.

#### Signature

- It is a **public** method.
- It accepts the following parameter(s):
    - `$state_machine` ([`StateMachineInterface`](../../Workflux/StateMachine/StateMachineInterface.md))
- It returns a(n) `string` value.

