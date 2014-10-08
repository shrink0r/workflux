<?php

namespace Workflux\Parser\Xml;

use Workflux\Parser\ParserInterface;
use Workflux\Error\Error;
use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachine;
use DOMDocument;
use DOMXpath;
use DOMElement;
use DOMException;
use LibXMLError;

class StateMachineDefinitionParser implements ParserInterface
{
    const XSD_SCHMEMA_FILE = 'workflux.xsd';

    const NAMESPACE_PREFIX = 'wf';

    protected $xpath;

    public function parse($state_machine_xml_file)
    {
        $this->setUp($state_machine_xml_file);

        $state_machines = [];
        foreach ($this->query('//state_machines/state_machine') as $state_machine_node) {
            $state_machine = $this->parseStateMachineNode($state_machine_node);
            $state_machines[$state_machine['name']] = $state_machine;
        }

        $this->tearDown();

        return $state_machines;
    }

    protected function setUp($state_machine_xml_file)
    {
        if (!is_readable($state_machine_xml_file)) {
            throw new Error(
                sprintf("Unable to read fsm definition file at location: %s", $state_machine_xml_file)
            );
        }

        $document = $this->createDocument($state_machine_xml_file);
        $this->xpath = new DOMXpath($document);
        $root_namespace = $document->lookupNamespaceUri($document->namespaceURI);
        $this->xpath->registerNamespace(self::NAMESPACE_PREFIX, $root_namespace);
    }

    protected function tearDown()
    {
        unset($this->xpath);
    }

    protected function createDocument($state_machine_xml_file)
    {
        $document = new DOMDocument();

        $user_error_handling = $this->enableErrorHandling();
        $document->load($state_machine_xml_file);
        $this->handleErrors(
            'Loading the document failed. Details are:' . PHP_EOL . PHP_EOL,
            PHP_EOL . 'Please fix the mentioned errors.',
            $user_error_handling
        );

        $this->validateXml($document);

        return $document;
    }

    protected function validateXml(DOMDocument $state_machine_doc)
    {
        $schema_path = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . self::XSD_SCHMEMA_FILE;

        $user_error_handling = $this->enableErrorHandling();
        if (!$state_machine_doc->schemaValidate($schema_path)) {
            throw new Error("The given state machine xml file does not validate against the workflux schema.");
        }
        $this->handleErrors(
            'Validating the document failed. Details are:' . PHP_EOL . PHP_EOL,
            PHP_EOL . 'Please fix the mentioned errors or use another schema file.',
            $user_error_handling
        );
    }

    protected function enableErrorHandling()
    {
        $user_error_handling = libxml_use_internal_errors(true);
        libxml_clear_errors();

        return $user_error_handling;
    }

    protected function handleErrors($msg_prefix = '', $msg_suffix = '', $user_error_handling = false)
    {
        if (libxml_get_last_error() !== false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($user_error_handling);

            throw new DOMException(
                $msg_prefix .
                $this->getErrorMessage($errors) .
                $msg_suffix
            );
        }

        libxml_use_internal_errors($user_error_handling);
    }

    protected function getErrorMessage(array $errors)
    {
        $error_message = '';
        foreach ($errors as $error) {
            $error_message .= $this->parseError($error) . PHP_EOL . PHP_EOL;
        }

        return $error_message;
    }

    protected function parseError(LibXMLError $error)
    {
        $msg = '';
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $msg .= 'Warning ' . $error->code . ': ';
                break;
            case LIBXML_ERR_FATAL:
                $msg .= 'Fatal error: ' . $error->code . ': ';
                break;
            case LIBXML_ERR_ERROR:
            default:
                $msg .= 'Error ' . $error->code . ': ';
                break;
        }

        $msg .= implode(
            PHP_EOL,
            [ trim($error->message), '  Line: ' . $error->line, 'Column: ' . $error->column ]
        );

        if ($error->file) {
            $msg .= PHP_EOL . '  File: ' . $error->file;
        }

        return $msg;
    }

    protected function query($xpath_expression, DOMElement $context = null)
    {
        $search = [ '~/(\w+)~', '~^(\w+)$~' ];
        $replace = [ sprintf('/%s:$1', self::NAMESPACE_PREFIX), sprintf('%s:$1', self::NAMESPACE_PREFIX) ];
        $namespaced_expression = preg_replace($search, $replace, $xpath_expression);

        return $this->xpath->query($namespaced_expression, $context);
    }

    protected function parseStateMachineNode(DOMElement $state_machine_node)
    {
        $state_machine_name = $state_machine_node->getAttribute('name');

        $state_nodes_data = [];
        $state_node_expressions = [ 'initial', 'state', 'final' ];
        foreach ($state_node_expressions as $state_node_expression) {
            foreach ($this->query($state_node_expression, $state_machine_node) as $state_node) {
                $state_node_data = $this->parseStateNode($state_node);
                $state_name = $state_node_data['name'];
                $state_nodes_data[$state_name] = $state_node_data;
            }
        }

        return [ 'name' => $state_machine_name, 'states' => $state_nodes_data ];
    }

    protected function parseStateNode(DOMElement $state_node)
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

        $state_class = null;
        if ($state_node->hasAttribute('class')) {
            $state_class = $state_node->getAttribute('class');
        }

        return [
            'name' => $state_node->getAttribute('name'),
            'type' => $state_type,
            'class' => $state_class,
            'options' => $this->parseOptions($state_node),
            'events' => array_merge(
                $this->parseStateNodeEventOuts($state_node),
                $this->parseStateNodeSequentialOuts($state_node)
            )
        ];
    }

    protected function parseStateNodeEventOuts(DOMElement $state_node)
    {
        $events = [];
        foreach ($this->query('event', $state_node) as $event_node) {
            $event_data = $this->parseEventNode($event_node);
            $event_name = $event_data['name'];
            $events[$event_name] = $event_data;
        }

        return $events;
    }

    protected function parseStateNodeSequentialOuts(DOMElement $state_node)
    {
        $seq_transitions = [];
        foreach ($this->query('transition', $state_node) as $transition_node) {
            $seq_transitions[] = $this->parseTransitionNode($transition_node);
        }

        return [ StateMachine::SEQ_TRANSITIONS_KEY => $seq_transitions ];
    }

    protected function parseEventNode(DOMElement $event_node)
    {
        $event_name = $event_node->getAttribute('name');
        $transitions = [];
        foreach ($this->query('transition', $event_node) as $transition_node) {
            $transitions[] = $this->parseTransitionNode($transition_node);
        }

        return [ 'name' => $event_name, 'transitions' => $transitions ];
    }

    protected function parseTransitionNode(DOMElement $transition_node)
    {
        $outgoing_state_name = $transition_node->getAttribute('target');
        $guard_node = $this->query('guard', $transition_node)->item(0);

        if ($guard_node) {
            $guard_data = $this->parseGuardNode($guard_node);
        } else {
            $guard_data = null;
        }

        return [ 'outgoing_state_name' => $outgoing_state_name, 'guard' => $guard_data ];
    }

    protected function parseGuardNode(DOMElement $guard_node)
    {
        return [
            'class' => $guard_node->getAttribute('class'),
            'options' => $this->parseOptions($guard_node)
        ];
    }

    protected function parseOptions(DOMElement $options_context)
    {
        $options = [];

        foreach ($this->query('option', $options_context) as $option_node) {
            if ($option_node->hasAttribute('name')) {
                $option_index = $option_node->getAttribute('name');
            } else {
                $option_index = count($options);
            }

            $child_options = $this->query('option', $option_node);
            if ($child_options->length > 0) {
                $option_value = $this->parseOptions($option_node);
            } else {
                $option_value = $this->literalize($option_node->nodeValue);
            }

            $options[$option_index] = $option_value;
        }

        return $options;
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
        $truthy_values = [ 'on', 'yes', 'true' ];
        $falsy_values = [ 'off', 'no', 'false' ];
        if (in_array($lowercase_value, $truthy_values, true)) {
            return true;
        } elseif (in_array($lowercase_value, $falsy_values, true)) {
            return false;
        }

        return $value;
    }
}
