<?php

namespace Workflux\Renderer;

use Workflux\StateMachine\StateMachineInterface;
use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\Transition\TransitionInterface;
use Params\Immutable\ImmutableOptions;

class DotGraphRenderer extends AbstractRenderer
{
    const DOT_TEMPLATE = <<<DOT
digraph %s {
%s

%s
}
DOT;

    const STATE_NODE_COLOR = '#607d8b';

    const STATE_NODE_FONTCOLOR = '#000000';

    const EDGE_FONTCOLOR = '#7f8c8d';

    const EDGE_PROMOTE_COLOR = '#2ecc71';

    const EDGE_DEMOTE_COLOR = '#3498db';

    const EDGE_DEFAULT_COLOR = '#607d8b';

    protected $node_id_map;

    protected $styles;

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

    protected function setUp(StateMachineInterface $state_machine)
    {
        $this->node_id_map = $this->buildNodeIdMap($state_machine);
        $this->styles = $this->getOption('style', new ImmutableOptions());
    }

    protected function buildNodeIdMap(StateMachineInterface $state_machine)
    {
        $node_id_map = [];
        $node_number = 1;
        foreach ($state_machine->getStates() as $state) {
            $node_id_map[$state->getName()] = sprintf('node%d', $node_number++);
        }

        return $node_id_map;
    }

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

    protected function getStyle($path, $default = null)
    {
        $style = $this->styles->getValues($path);

        return $style ?: $default;
    }

    protected function tearDown()
    {
        unset($this->node_id_map);
        unset($this->styles);
    }
}
