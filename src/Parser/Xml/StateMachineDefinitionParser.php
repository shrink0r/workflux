<?php

namespace Workflux\Parser\Xml;

use Workflux\Parser\IParser;
use Workflux\Error\Error;
use Workflux\State\IState;
use Workflux\StateMachine\StateMachine;
use DOMDocument;
use DOMXpath;
use DOMElement;

class StateMachineDefinitionParser implements IParser
{
    protected $xpath;

    public function parse($state_machine_definition_file)
    {
        if (!is_readable($state_machine_definition_file)) {
            throw new Error(
                sprintf("Unable to read fsm definition file at location: %s", $state_machine_definition_file)
            );
        }

        $document = new DOMDocument();
        $document->load($state_machine_definition_file);
        // @todo write xsd and use it here to validate stuff.
        $this->xpath = new DOMXpath($document);

        return $this->parseStateMachineNode(
            $this->xpath->query('//state_machine')->item(0)
        );
    }

    protected function parseStateMachineNode(DOMElement $state_machine_node)
    {
        $state_machine_name = $state_machine_node->getAttribute('name');

        $state_nodes_data = [];
        $state_node_expressions = [ 'initial', 'state', 'final' ];
        foreach ($state_node_expressions as $state_node_expression) {
            foreach ($this->xpath->query($state_node_expression, $state_machine_node) as $state_node) {
                $state_node_data = $this->parseStateNode($state_node);
                $state_name = $state_node_data['name'];
                $state_nodes_data[$state_name] = $state_node_data;
            }
        }

        return [ 'name' => $state_machine_name, 'states' => $state_nodes_data ];
    }

    protected function parseStateNode(DOMElement $state_node)
    {
        $state_name = $state_node->getAttribute('name');
        $events = [];
        foreach ($this->xpath->query('event', $state_node) as $event_node) {
            $event_data = $this->parseEventNode($event_node);
            $event_name = $event_data['name'];
            $events[$event_name] = $event_data;
        }
        $seq_transitions = [];
        foreach ($this->xpath->query('transition', $state_node) as $transition_node) {
            $seq_transitions[] = $this->parseTransitionNode($transition_node);
        }
        $events[StateMachine::SEQ_TRANSITIONS_KEY] = $seq_transitions;

        switch ($state_node->nodeName) {
            case 'initial':
                $state_type = IState::TYPE_INITIAL;
                break;
            case 'final':
                $state_type = IState::TYPE_FINAL;
                break;
            default:
                $state_type = IState::TYPE_ACTIVE;
        }

        return [ 'name' => $state_name, 'events' => $events, 'type' => $state_type ];
    }

    protected function parseEventNode(DOMElement $event_node)
    {
        $event_name = $event_node->getAttribute('name');
        $transitions = [];
        foreach ($this->xpath->query('transition', $event_node) as $transition_node) {
            $transitions[] = $this->parseTransitionNode($transition_node);
        }

        return [ 'name' => $event_name, 'transitions' => $transitions ];
    }

    protected function parseTransitionNode(DOMElement $transition_node)
    {
        $outgoing_state_name = $transition_node->getAttribute('target');
        $guard_node = $this->xpath->query('guard', $transition_node)->item(0);

        if (!$guard_node) {
            $guard_data = null;
        } else {
            $guard_data = $this->parseGuardNode($guard_node);
        }

        return [ 'outgoing_state_name' => $outgoing_state_name, 'guard' => $guard_data ];
    }

    protected function parseGuardNode(DOMElement $guard_node)
    {
        $guard_class = $guard_node->getAttribute('class');
        $guard_options = [];
        foreach ($this->xpath->query('option', $guard_node) as $option_node) {
            $option_name = $option_node->getAttribute('name');
            $guard_options[$option_name] = $this->literalize($option_node->nodeValue);
        }

        return [ 'class' => $guard_class, 'options' => $guard_options ];
    }

    protected function literalize($value)
    {
        if (is_int($value)) {
            return (int)$value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);
        if ($value == '') {
            return null;
        }

        $lowercase_value = strtolower($value);
        if ($lowercase_value === 'on' || $lowercase_value === 'yes' || $lowercase_value === 'true') {
            return true;
        } elseif ($lowercase_value === 'off' || $lowercase_value === 'no' || $lowercase_value === 'false') {
            return false;
        }

        return $value;
    }
}
