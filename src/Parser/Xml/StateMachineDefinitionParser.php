<?php

namespace Workflux\Parser\Xml;

use Workflux\Error\Error;
use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachine;
use DOMElement;

/**
 * The StateMachineDefinitionParser can parse xml state machine definitions and provides an array,
 * that is structured the way the StateMachineBuilder expects.
 */
class StateMachineDefinitionParser extends AbstractXmlParser
{
    /**
     * Returns the namespace prefix to use when running xpath queries.
     *
     * @return string
     */
    protected function getNamespacePrefix()
    {
        return 'wf';
    }

    /**
     * Return an absolute file system path pointing to the xsd schema file to use for validation.
     *
     * @return string
     */
    protected function getSchemaPath()
    {
        return dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'workflux.xsd';
    }

    /**
     * Parses the given xml file and returns an array of state machine definition arrays.
     *
     * @return mixed
     */
    protected function doParse()
    {
        $state_machines = [];
        foreach ($this->xpath->query('//state_machines/state_machine') as $state_machine_node) {
            $state_machine = $this->parseStateMachineNode($state_machine_node);
            $state_machines[$state_machine['name']] = $state_machine;
        }

        return $state_machines;
    }

    /**
     * Returns an array representation of the given 'state_machine' node.
     *
     * @param DOMElement $state_machine_node
     *
     * @return array
     */
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

        if ($state_machine_node->hasAttribute('class')) {
            $state_machine_class = $state_machine_node->getAttribute('class');
        } else {
            $state_machine_class = null;
        }

        return [
            'class' => $state_machine_class,
            'name' => $state_machine_name,
            'states' => $state_nodes_data
        ];
    }

    /**
     * Returns an array representation of the given 'state' node.
     *
     * @param DOMElement $state_node
     *
     * @return array
     */
    protected function parseStateNode(DOMElement $state_node)
    {
        $state_class = null;
        if ($state_node->hasAttribute('class')) {
            $state_class = $state_node->getAttribute('class');
        }

        return [
            'name' => $state_node->getAttribute('name'),
            'type' => $this->parseStateType($state_node),
            'class' => $state_class,
            'options' => $this->options_parser->parse($state_node),
            'events' => array_merge(
                $this->parseStateNodeEventOuts($state_node),
                $this->parseStateNodeSequentialOuts($state_node)
            )
        ];
    }

    /**
     * Maps the node name of the given state node to it's relating StateInterface::TYPE_* constant.
     *
     * @param DOMElement $state_node
     *
     * @return string One of StateInterface::TYPE_INITIAL, -TYPE_FINAL or -TYPE_ACTIVE.
     */
    protected function parseStateType(DOMElement $state_node)
    {
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

        return $state_type;
    }

    /**
     * Parses the given 'state' node and creates an array of event transitions.
     *
     * @param DOMElement $state_node
     *
     * @return array
     */
    protected function parseStateNodeEventOuts(DOMElement $state_node)
    {
        $events = [];
        foreach ($this->xpath->query('event', $state_node) as $event_node) {
            $event_data = $this->parseEventNode($event_node);
            $event_name = $event_data['name'];
            $events[$event_name] = $event_data;
        }

        return $events;
    }

    /**
     * Parses the given 'state' node and creates an array of sequential transitions.
     *
     * @param DOMElement $state_node
     *
     * @return array
     */
    protected function parseStateNodeSequentialOuts(DOMElement $state_node)
    {
        $seq_transitions = [];
        foreach ($this->xpath->query('transition', $state_node) as $transition_node) {
            $seq_transitions[] = $this->parseTransitionNode($transition_node);
        }

        return [ StateMachine::SEQ_TRANSITIONS_KEY => $seq_transitions ];
    }

    /**
     * Returns an array representation of the given 'event' node.
     *
     * @param DOMElement $event_node
     *
     * @return array
     */
    protected function parseEventNode(DOMElement $event_node)
    {
        $event_name = $event_node->getAttribute('name');
        $transitions = [];
        foreach ($this->xpath->query('transition', $event_node) as $transition_node) {
            $transitions[] = $this->parseTransitionNode($transition_node);
        }

        return [
            'name' => $event_name,
            'transitions' => $transitions
        ];
    }

    /**
     * Returns an array representation of the given 'transition' node.
     *
     * @param DOMElement $transition_node
     *
     * @return array
     */
    protected function parseTransitionNode(DOMElement $transition_node)
    {
        $guard_node = $this->xpath->query('guard', $transition_node)->item(0);
        if ($guard_node !== null && !$guard_node instanceof DOMElement) {
            throw new Error("Invalid guard node given.");
        }

        return [
            'outgoing_state_name' => $transition_node->getAttribute('target'),
            'guard' => is_null($guard_node) ? null : $this->parseGuardNode($guard_node)
        ];
    }

    /**
     * Returns an array representation of the given 'guard' node.
     *
     * @param DOMElement $guard_node
     *
     * @return array
     */
    protected function parseGuardNode(DOMElement $guard_node)
    {
        return [
            'class' => $guard_node->getAttribute('class'),
            'options' => $this->options_parser->parse($guard_node)
        ];
    }
}
