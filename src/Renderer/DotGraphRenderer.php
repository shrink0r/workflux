<?php

namespace Workflux\Renderer;

use Workflux\StateMachine\IStateMachine;
use Workflux\StateMachine\StateMachine;
use Workflux\Transition\ITransition;
use Params\Immutable\ImmutableOptions;

class DotGraphRenderer extends AbstractRenderer
{
    const STATE_NODE_COLOR = '#607d8b';

    const STATE_NODE_FONTCOLOR = '#000000';

    const EDGE_FONTCOLOR = '#7f8c8d';

    const EDGE_PROMOTE_COLOR = '#2ecc71';

    const EDGE_DEMOTE_COLOR = '#3498db';

    const EDGE_DEFAULT_COLOR = '#607d8b';

    protected $node_id_map;

    protected $styles;

    public function renderGraph(IStateMachine $state_machine)
    {
        $this->node_id_map = $this->buildNodeIdMap($state_machine);
        $this->styles = $this->getOption('style', new ImmutableOptions());

        $dot_code = sprintf(
            $this->getDotCodeTemplate(),
            $state_machine->getName(),
            implode(PHP_EOL, $this->getNodes($state_machine)),
            implode(PHP_EOL, $this->getEdges($state_machine))
        );

        $this->node_id_map = null;
        $this->styles = null;

        return $dot_code;
    }

    protected function buildNodeIdMap(IStateMachine $state_machine)
    {
        $node_id_map = [];
        $node_number = 1;
        foreach ($state_machine->getStates() as $state) {
            $node_id_map[$state->getName()] = sprintf('node%d', $node_number++);
        }

        return $node_id_map;
    }

    protected function getNodes(IStateMachine $state_machine)
    {
        $state_nodes = [];
        foreach ($state_machine->getStates() as $state_name => $state) {
            $fontcolor = $this->styles->getValues('state_node.fontcolor');
            $color = $this->styles->getValues('state_node.color');

            $attributes = [
                sprintf('label="%s"', $state_name),
                sprintf('fontcolor="%s"', $fontcolor ?: self::STATE_NODE_FONTCOLOR),
                sprintf('color="%s"', $color ?: self::STATE_NODE_COLOR)
            ];

            if ($state->isFinal()) {
                $attributes[] = 'style="bold"';
            }
            if (!$state_machine->isEventState($state_name) && !$state->isFinal()) {
                $attributes[] = 'shape="parallelogram"';
            }

            $state_nodes[] = sprintf('%s [%s]', $this->node_id_map[$state_name], implode(' ', $attributes));
        }

        $state_nodes[] = sprintf(
            '0 [label="X" fontsize="13" margin="0" fontname="arial" width="0.15" color="%s" shape="circle"]',
            self::STATE_NODE_COLOR
        );

        return $state_nodes;
    }

    protected function getEdges(IStateMachine $state_machine)
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
        $color = $this->styles->getValues('edge.colors.default');
        $edges[] = sprintf(
            '0 -> %s [color="%s"]',
            $this->node_id_map[$state_name],
            $color ?: self::EDGE_DEFAULT_COLOR
        );

        return $edges;
    }

    protected function createEdge(ITransition $transition, $state_name, $event_name)
    {
        $from_node = $this->node_id_map[$state_name];
        $to_node = $this->node_id_map[$transition->getOutgoingStateName()];

        $transition_label = $event_name === StateMachine::SEQ_TRANSITIONS_KEY ? '' : $event_name;
        if ($transition->hasGuard()) {
            $transition_label .= $transition->getGuard();
        }

        $fontcolor = $this->styles->getValues('edge.fontcolor');
        $attributes = [
            sprintf('label="%s"', $transition_label),
            sprintf('fontcolor="%s"', $fontcolor ?: self::EDGE_FONTCOLOR),
        ];

        if ($event_name === 'promote') {
            $color = $this->styles->getValues('edge.colors.promote');
            $color = $color ?: self::EDGE_PROMOTE_COLOR;
        } elseif ($event_name === 'demote') {
            $color = $this->styles->getValues('edge.colors.demote');
            $color = $color ?: self::EDGE_DEMOTE_COLOR;
        } else {
            $color = $this->styles->getValues('edge.colors.default');
            $color = $color ?: self::EDGE_DEFAULT_COLOR;
        }
        $attributes[] = sprintf('color="%s"', $color);

        return sprintf('%s -> %s [%s]', $from_node, $to_node, implode(' ', $attributes));
    }

    protected function getDotCodeTemplate()
    {
        return <<<DOT
digraph %s {
%s

%s
}
DOT;
    }
}
