<?php

namespace Workflux\Parser\Xml;

use Workflux\Parser\ParserInterface;
use Workflux\Error\Error;
use Workflux\State\StateInterface;
use Workflux\StateMachine\StateMachine;
use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;
use DOMDocument;
use DOMXpath;
use DOMElement;
use DOMException;
use LibXMLError;

/**
 * The StateMachineDefinitionParser can parse xml state machine definitions and provides an array,
 * that is structured the way the StateMachineBuilder expects.
 */
class StateMachineDefinitionParser implements ParserInterface
{
    use ImmutableOptionsTrait;

    /**
     * @var XSD_SCHMEMA_FILE
     */
    const XSD_SCHMEMA_FILE = 'workflux.xsd';

    /**
     * @var NAMESPACE_PREFIX
     */
    const NAMESPACE_PREFIX = 'wf';

    /**
     * @var array $options
     */
    protected $xpath;

    /**
     * Creates a new StateMachineDefinitionParser instance.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = new ImmutableOptions($options);
    }

    /**
     * Parses the given xml file and returns an array of state machine definition arrays.
     *
     * @param string $state_machine_xml_file Vaild filesystem path to a xml file defining state machines.
     *
     * @return array
     */
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

    /**
     * Sets up the internal state before the actual parsing is started.
     *
     * @param string $state_machine_xml_file
     */
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

    /**
     * Tears down the internal state after parsing the payload.
     */
    protected function tearDown()
    {
        unset($this->xpath);
    }

    /**
     * Creates a new DOMDocument, loads and schema-validates the xml from the given xml file path.
     *
     * @param string $state_machine_xml_file
     *
     * @return DOMDocument
     */
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

    /**
     * Validates the given DOMDocument against the workflux xsd schema.
     *
     * @param DOMDocument $state_machine_doc
     *
     * @throws Error | DOMException
     */
    protected function validateXml(DOMDocument $state_machine_doc)
    {
        $default_schema_path = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . self::XSD_SCHMEMA_FILE;
        $schema_path = $this->getOption('schema', $default_schema_path);

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

    /**
     * Disables libxml errors, allowing the parser to take care of the errors by itself.
     *
     * @return bool The former "use_errors" value.
     */
    protected function enableErrorHandling()
    {
        $user_error_handling = libxml_use_internal_errors(true);
        libxml_clear_errors();

        return $user_error_handling;
    }

    /**
     * Checks for internal libxml errors and throws them in form of a single DOMException.
     * If no errors occured, then nothing happens.
     *
     * @param string $msg_prefix Is prepended to the libxml error message.
     * @param string $msg_suffix Is appended to the libxml error message.
     * @param bool $user_error_handling Allows to enable or disable internal libxml error handling.
     *
     * @throws DOMException
     */
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

    /**
     * Converts a given list of libxml errors into an error report.
     *
     * @param array $errors
     *
     * @return string
     */
    protected function getErrorMessage(array $errors)
    {
        $error_message = '';
        foreach ($errors as $error) {
            $error_message .= $this->parseError($error) . PHP_EOL . PHP_EOL;
        }

        return $error_message;
    }

    /**
     * Converts a given libxml error into an error message.
     *
     * @param LibXMLError $error
     *
     * @return string
     */
    protected function parseError(LibXMLError $error)
    {
        $prefix_map = [
            LIBXML_ERR_WARNING => '[warning]',
            LIBXML_ERR_FATAL => '[fatal]',
            LIBXML_ERR_ERROR => '[error]'
        ];
        $prefix = isset($prefix_map[$error->level]) ? $prefix_map[$error->level] : $prefix_map[LIBXML_ERR_ERROR];

        $msg_parts = [];
        $msg_parts[] = sprintf('%s %s: %s', $prefix, $error->level, trim($error->message));
        $msg_parts[] = sprintf('Line: %d', $error->line);
        $msg_parts[] = sprintf('Column: %d', $error->column);
        if ($error->file) {
            $msg_parts[] = sprintf('File: %s', $error->file);
        }

        return implode(PHP_EOL, $msg_parts);
    }

    /**
     * Takes an xpath expression and preprends the parser's namespace prefix to each xpath segment.
     * Then it runs the namespaced expression and returns the result.
     * Example: '//state_machines/state_machine' - expands to -> '//wf:state_machines/wf:state_machine'
     *
     * @param string $xpath_expression Non namespaced xpath expression.
     * @param DOMElement $context Allows to pass a context node that is used for the actual xpath query.
     *
     * @return DOMNodeList
     */
    protected function query($xpath_expression, DOMElement $context = null)
    {
        $search = [ '~/(\w+)~', '~^(\w+)$~' ];
        $replace = [ sprintf('/%s:$1', self::NAMESPACE_PREFIX), sprintf('%s:$1', self::NAMESPACE_PREFIX) ];
        $namespaced_expression = preg_replace($search, $replace, $xpath_expression);

        return $this->xpath->query($namespaced_expression, $context);
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
            foreach ($this->query($state_node_expression, $state_machine_node) as $state_node) {
                $state_node_data = $this->parseStateNode($state_node);
                $state_name = $state_node_data['name'];
                $state_nodes_data[$state_name] = $state_node_data;
            }
        }

        return [ 'name' => $state_machine_name, 'states' => $state_nodes_data ];
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
        foreach ($this->query('event', $state_node) as $event_node) {
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
        foreach ($this->query('transition', $state_node) as $transition_node) {
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
        foreach ($this->query('transition', $event_node) as $transition_node) {
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
        $guard_node = $this->query('guard', $transition_node)->item(0);
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
            'options' => $this->parseOptions($guard_node)
        ];
    }

    /**
     * Returns an array representation of all option nodes below the given context node.
     *
     * @param DOMElement $options_context
     *
     * @return array
     */
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

    /**
     * Takes a xml node value and casts it to it's php scalar counterpart.
     *
     * @param string $value
     *
     * @return string | boolean | int
     */
    protected function literalize($value)
    {
        if (preg_match('/^\d+$/', $value)) {
            return (int)$value;
        } else {
            return $this->literalizeString($value);
        }
    }

    /**
     * Takes an xml node value and returns it either as a string or boolean.
     *
     * @param string $value Following values are cast to bool true/false: on, yes, true/off, no, false
     *
     * @return string | boolean
     */
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
