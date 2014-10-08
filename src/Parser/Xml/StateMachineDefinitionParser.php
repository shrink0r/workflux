<?php

namespace Workflux\Parser\Xml;

use Workflux\Parser\ParserInterface;
use Workflux\Error\Error;
use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachine;
use DOMDocument;
use DOMXpath;
use DOMElement;

class StateMachineDefinitionParser implements ParserInterface
{
    const XSD_SCHMEMA_FILE = 'workflux.xsd';

    protected $xpath;

    public function parse($state_machine_definition_file)
    {
        if (!is_readable($state_machine_definition_file)) {
            throw new Error(
                sprintf("Unable to read fsm definition file at location: %s", $state_machine_definition_file)
            );
        }

        $schema_path = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . self::XSD_SCHMEMA_FILE;
        $document = new DOMDocument();
        $document->load($state_machine_definition_file);
        $document->schemaValidate($schema_path);

        $this->xpath = new DOMXpath($document);
        $root_namespace = $document->lookupNamespaceUri($document->namespaceURI);
        $this->xpath->registerNamespace('wf', $root_namespace);

        $state_machines = [];
        foreach ($this->xpath->query('//wf:state_machines/wf:state_machine') as $state_machine_node) {
            $state_machine = $this->parseStateMachineNode($state_machine_node);
            $state_machines[$state_machine['name']] = $state_machine;
        }

        return $state_machines;
    }

    protected function parseStateMachineNode(DOMElement $state_machine_node)
    {
        $state_machine_name = $state_machine_node->getAttribute('name');

        $state_nodes_data = [];
        $state_node_expressions = [ 'wf:initial', 'wf:state', 'wf:final' ];
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
        foreach ($this->xpath->query('wf:event', $state_node) as $event_node) {
            $event_data = $this->parseEventNode($event_node);
            $event_name = $event_data['name'];
            $events[$event_name] = $event_data;
        }
        $seq_transitions = [];
        foreach ($this->xpath->query('wf:transition', $state_node) as $transition_node) {
            $seq_transitions[] = $this->parseTransitionNode($transition_node);
        }
        $events[StateMachine::SEQ_TRANSITIONS_KEY] = $seq_transitions;

        switch ($state_node->nodeName) {
            case 'initial':
                $state_type = StateInterface::TYPE_INITIAL;
                break;
            case 'final':
                $state_type = StateInterface::TYPE_FINAL;
                break;
            default:
                $state_type = StateInterface::TYPE_ACTIVE;
        }

        $state_class = null;
        if ($state_node->hasAttribute('class')) {
            $state_class = $state_node->getAttribute('class');
        }

        return [
            'name' => $state_name,
            'events' => $events,
            'type' => $state_type,
            'class' => $state_class
        ];
    }

    protected function parseEventNode(DOMElement $event_node)
    {
        $event_name = $event_node->getAttribute('name');
        $transitions = [];
        foreach ($this->xpath->query('wf:transition', $event_node) as $transition_node) {
            $transitions[] = $this->parseTransitionNode($transition_node);
        }

        return [ 'name' => $event_name, 'transitions' => $transitions ];
    }

    protected function parseTransitionNode(DOMElement $transition_node)
    {
        $outgoing_state_name = $transition_node->getAttribute('target');
        $guard_node = $this->xpath->query('wf:guard', $transition_node)->item(0);

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
        foreach ($this->xpath->query('wf:option', $guard_node) as $option_node) {
            $option_name = $option_node->getAttribute('name');
            $guard_options[$option_name] = $this->literalize($option_node->nodeValue);
        }

        return [ 'class' => $guard_class, 'options' => $guard_options ];
    }

    protected function literalize($value)
    {
        if (preg_match('/^\d+$/', $value)) {
            return (int)$value;
        } else {
            return $this->literalizeString($value);
        }
    }

    protected function literalizeString($value)
    {
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
