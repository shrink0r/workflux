<?php

namespace Workflux\Renderer;

use Workflux\StateMachine\IStateMachine;

class DotGraphRenderer implements IGraphRenderer
{
    public function renderGraph(IStateMachine $state_machine)
    {
        $node_id_map = $this->buildNodeIdMap($state_machine);

        return sprintf(
            $this->getDotCodeTemplate(),
            $state_machine->getName(),
            implode(PHP_EOL, $this->getNodes($state_machine, $node_id_map)),
            implode(PHP_EOL, $this->getEdges($state_machine, $node_id_map))
        );
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

    protected function getNodes(IStateMachine $state_machine, array $node_id_map)
    {
        $state_nodes = [];
        foreach ($state_machine->getStates() as $state_name => $state) {
            $state_nodes[] = sprintf('%s [label="%s"]', $node_id_map[$state_name], $state_name);
        }

        return $state_nodes;
    }

    protected function getEdges(IStateMachine $state_machine, array $node_id_map)
    {
        $edges = [];
        foreach ($state_machine->getTransitions() as $state_name => $state_transitions) {
            foreach ($state_transitions as $event_name => $transitions) {
                foreach ($transitions as $transition) {
                    $from_node = $node_id_map[$state_name];
                    $to_node = $node_id_map[$transition->getOutgoingStateName()];
                    $edges[] = sprintf('%s -> %s [label="%s"]', $from_node, $to_node, $event_name);
                }
            }
        }

        return $edges;
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
