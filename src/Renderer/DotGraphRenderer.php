<?php

namespace Workflux\Renderer;

use Workflux\Error\Error;
use Workflux\StateMachine\StateMachineInterface;
use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\Transition\TransitionInterface;
use Params\Immutable\ImmutableOptions;

/**
 * The DotGraphRenderer can render state machines as dot-graphs.
 * It supports various options for changing the colors, shapes etc. that are used to style the graph.
 */
class DotGraphRenderer extends AbstractRenderer
{
    /**
     * @var string DOT_TEMPLATE
     */
    const DOT_TEMPLATE = <<<DOT
digraph %s {
%s

%s
}
DOT;

    /**
     * @var string STATE_NODE_COLOR
     */
    const STATE_NODE_COLOR = '#607d8b';

    /**
     * @var string STATE_NODE_FONTCOLOR
     */
    const STATE_NODE_FONTCOLOR = '#000000';

    /**
     * @var string EDGE_FONTCOLOR
     */
    const EDGE_FONTCOLOR = '#7f8c8d';

    /**
     * @var string EDGE_PROMOTE_COLOR
     */
    const EDGE_PROMOTE_COLOR = '#2ecc71';

    /**
     * @var string EDGE_DEMOTE_COLOR
     */
    const EDGE_DEMOTE_COLOR = '#3498db';

    /**
     * @var string EDGE_DEFAULT_COLOR
     */
    const EDGE_DEFAULT_COLOR = '#607d8b';

    /**
     * @var array $node_id_map
     */
    protected $node_id_map;

    /**
     * @var ImmutableOptions $styles
     */
    protected $styles;

    /**
     * Renders the given state machine as a dot-graph.
     *
     * @param StateMachineInterface $state_machine
     *
     * @return string
     */
    public function renderGraph(StateMachineInterface $state_machine)
    {
        $this->setUp($state_machine);

        $dot_code = sprintf(
            self::DOT_TEMPLATE,
            $state_machine->getName(),
            implode(PHP_EOL, $this->getNodes($state_machine)),
            implode(PHP_EOL, $this->getEdges($state_machine))
        );

        $this->tearDown();

        return $dot_code;
    }

    /**
     * Sets up the renderer's internal state before rendering.
     *
     * @param StateMachineInterface $state_machine
     */
    protected function setUp(StateMachineInterface $state_machine)
    {
        $styles = $this->getOption('style', new ImmutableOptions());
        if (!$styles instanceof ImmutableOptions) {
            throw new Error(
                sprintf(
                    'Encountered unexpected value type for "styles" option. Expected instance of %s',
                    ImmutableOptions::CLASS
                )
            );
        }

        $this->styles = $styles;
        $this->node_id_map = $this->buildNodeIdMap($state_machine);
    }

    /**
     * Takes a state machine and returns an array of dot-graph node ids.
     *
     * @param StateMachineInterface $state_machine
     *
     * @return array An assoc array mapping state_names (keys) to node-ids (values).
     */
    protected function buildNodeIdMap(StateMachineInterface $state_machine)
    {
        $node_id_map = [];
        $node_number = 1;
        foreach ($state_machine->getStates() as $state) {
            $node_id_map[$state->getName()] = sprintf('node%d', $node_number++);
        }

        return $node_id_map;
    }

    /**
     * Creates an array of dot-graph node from the given state machine.
     * One node is created for each state within the state machine.
     * Also the 'uml start node' is added in addition to the state nodes.
     *
     * @param StateMachineInterface $state_machine
     *
     * @return array An array of strings, that represent the particular dot-graph nodes.
     */
    protected function getNodes(StateMachineInterface $state_machine)
    {
        $state_nodes = [];
        foreach ($state_machine->getStates() as $state) {
            $state_nodes[] = $this->createStateNode($state_machine, $state);
        }

        $state_nodes[] = sprintf(
            '0 [label="X" fontsize="13" margin="0" fontname="arial" width="0.15" color="%s" shape="circle"]',
            self::STATE_NODE_COLOR
        );

        return $state_nodes;
    }

    /**
     * Creates a specific dot-graph node that represents the given state.
     *
     * @param StateMachineInterface $state_machine
     * @param StateInterface $state
     *
     * @return string
     */
    protected function createStateNode(StateMachineInterface $state_machine, StateInterface $state)
    {
        $state_name = $state->getName();

        $attributes = [
            sprintf('label="%s"', $state_name),
            sprintf('fontcolor="%s"', $this->getStyle('state_node.fontcolor', self::STATE_NODE_FONTCOLOR)),
            sprintf('color="%s"', $this->getStyle('state_node.color', self::STATE_NODE_COLOR))
        ];

        if ($state->isFinal()) {
            $attributes[] = 'style="bold"';
        }
        if (!$state_machine->isEventState($state_name) && !$state->isFinal()) {
            $attributes[] = 'shape="parallelogram"';
        }

        return sprintf('%s [%s]', $this->node_id_map[$state_name], implode(' ', $attributes));
    }

    /**
     * Creates an array of dot-graph edges that connect the (state)nodes of the dot-graph.
     *
     * @param StateMachineInterface $state_machine
     *
     * @return array An array of strings, that represent the particular dot-graph edges.
     */
    protected function getEdges(StateMachineInterface $state_machine)
    {
        $edges = [];
        foreach ($state_machine->getTransitions() as $state_name => $state_transitions) {
            foreach ($state_transitions as $event_name => $transitions) {
                foreach ($transitions as $transition) {
                    $edges[] = $this->createEdge($transition, $state_name, $event_name);
                }
            }
        }

        $state_name = $state_machine->getInitialState()->getName();
        $edges[] = sprintf(
            '0 -> %s [color="%s"]',
            $this->node_id_map[$state_name],
            $this->getStyle('edge.colors.default', self::EDGE_DEFAULT_COLOR)
        );

        return $edges;
    }

    /**
     * Creates a specific dot-graph edge for the given state-transition.
     *
     * @param TransitionInterface $transition
     * @param string $state_name
     * @param string $event_name
     *
     * @return string
     */
    protected function createEdge(TransitionInterface $transition, $state_name, $event_name)
    {
        $from_node = $this->node_id_map[$state_name];
        $to_node = $this->node_id_map[$transition->getOutgoingStateName()];

        $transition_label = $event_name === StateMachine::SEQ_TRANSITIONS_KEY ? '' : $event_name;
        if ($transition->hasGuard()) {
            $transition_label .= $transition->getGuard();
        }

        $attributes = [
            sprintf('label="%s"', $transition_label),
            sprintf('fontcolor="%s"', $this->getStyle('edge.fontcolor', self::EDGE_FONTCOLOR)),
        ];

        $attributes[] = sprintf('color="%s"', $this->getEdgeColor($event_name));

        return sprintf('%s -> %s [%s]', $from_node, $to_node, implode(' ', $attributes));
    }

    /**
     * Returns the color to use for drawing an edge based on the given event.
     * The color is first looked up within the 'styles' option and falls back to a class defined default.
     *
     * @param string $event_name
     *
     * @return string Either valid color name, rgb(a) or hex value.
     */
    protected function getEdgeColor($event_name)
    {
        switch ($event_name) {
            case 'promote':
                $color = $this->getStyle('edge.colors.promote', self::EDGE_PROMOTE_COLOR);
                break;
            case 'demote':
                $color = $this->getStyle('edge.colors.demote', self::EDGE_DEMOTE_COLOR);
                break;
            default:
                $color = $this->getStyle('edge.colors.default', self::EDGE_DEFAULT_COLOR);
        }

        return $color;
    }

    /**
     * Returns a specific value or values from the 'styles' option.
     *
     * @param string $value_path You can use jmespath expressions here: https://github.com/mtdowling/jmespath.php
     * @param mixed $default
     *
     * @return mixed Either the option value or the given default.
     */
    protected function getStyle($value_path, $default = null)
    {
        $style = $this->styles->getValues($value_path);

        return $style ?: $default;
    }

    /**
     * Resets the renderer's internal state after rendering.
     */
    protected function tearDown()
    {
        unset($this->node_id_map);
        unset($this->styles);
    }
}
