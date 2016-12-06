<?php

namespace Workflux\Renderer;

use Ds\Map;
use Workflux\Renderer\RendererInterface;
use Workflux\StateMachineInterface;

final class DotGraphRenderer implements RendererInterface
{
    /**
     * @param StateMachineInterface $state_machine
     *
     * @return mixed
     */
    public function render(StateMachineInterface $state_machine)
    {
        $node_id = 0;
        $node_id_map = new Map;
        foreach ($state_machine->getStates() as $state) {
            $node_id_map->put($state->getName(), sprintf('node%d', ++$node_id));
        }
        return sprintf(
            "digraph \"%s\" {\n    %s\n\n    %s\n}",
            $state_machine->getName(),
            implode("\n    ", $this->renderStateNodes($state_machine, $node_id_map)),
            implode("\n    ", $this->renderTransitionEdges($state_machine, $node_id_map))
        );
    }

    /**
     * @param  StateMachineInterface $state_machine
     * @param  Map $node_id_map
     *
     * @return string[]
     */
    private function renderStateNodes(StateMachineInterface $state_machine, Map $node_id_map): array
    {
        $state_nodes = [];
        foreach ($state_machine->getStates() as $state_name => $state) {
            $attributes = sprintf('label="%s"', $state_name);
            $attributes .= ' fontname="Arial" fontsize="13" fontcolor="#000000" color="#607d8b"';
            if ($state->isFinal() || $state->isInitial()) {
                $attributes .= ' style="bold"';
            }
            $state_nodes[] = sprintf('%s [%s];', $node_id_map->get($state_name), $attributes);
        }
        return $state_nodes;
    }

    /**
     * @param  StateMachineInterface $state_machine
     * @param  Map $node_id_map
     *
     * @return string[]
     */
    private function renderTransitionEdges(StateMachineInterface $state_machine, Map $node_id_map): array
    {
        $edges = [];
        foreach ($state_machine->getStateTransitions() as $state_name => $state_transitions) {
            foreach ($state_transitions as $transition) {
                $from_node = $node_id_map->get($transition->getFrom());
                $to_node = $node_id_map->get($transition->getTo());
                $transition_label = (string)$transition;
                $attributes = sprintf('label="%s" ', trim(addslashes($transition_label)));
                $attributes .= 'fontname="Arial" fontsize="12" fontcolor="#7f8c8d" color="#2ecc71"';
                $edges[] = sprintf('%s -> %s [%s];', $from_node, $to_node, $attributes);
            }
        }
        return $edges;
    }
}
