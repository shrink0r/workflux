<?php

namespace Workflux\Parser\Xml;

use Workflux\Parser\ParserInterface;
use Workflux\Error\Error;
use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;
use DOMDocument;
use DOMElement;
use DOMException;
use LibXMLError;

/**
 * The AbstsractXmlParser serves as base class for xml parsers.
 */
abstract class AbstractXmlParser implements ParserInterface
{
    use ImmutableOptionsTrait;

    /**
     * @var Xpath $xpath
     */
    protected $xpath;

    /**
     * @var OptionsXpathParser $options_parser
     */
    protected $options_parser;

    /**
     * Returns the namespace prefix to use when running xpath queries.
     *
     * @return string
     */
    protected abstract function getNamespacePrefix();

    /**
     * Return an absolute file system path pointing to the xsd schema file to use for validation.
     *
     * @return string
     */
    protected abstract function getSchemaPath();

    /**
     * Does the specific parsing and returns the corresponding result.
     *
     * @return mixed
     */
    protected abstract function doParse();

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
     * Parses the given xml file and returns the corresponding data.
     *
     * @param string $xml_file Vaild filesystem path to a xml file.
     *
     * @return mixed The parsed data.
     */
    public function parse($xml_file)
    {
        $this->setUp($xml_file);

        $result = $this->doParse();

        $this->tearDown();

        return $result;
    }

    /**
     * Sets up the internal state before the actual parsing is started.
     *
     * @param string $xml_file
     */
    protected function setUp($xml_file)
    {
        if (!is_readable($xml_file)) {
            throw new Error(sprintf("Unable to read fsm definition file at location: %s", $xml_file));
        }

        $document = $this->createDocument($xml_file);
        $this->xpath = new Xpath($document, $this->getNamespacePrefix());
        $this->options_parser = new OptionsXpathParser($this->xpath);
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
     * @param string $xml_file
     *
     * @return DOMDocument
     */
    protected function createDocument($xml_file)
    {
        $document = new DOMDocument();

        $user_error_handling = $this->enableErrorHandling();
        $document->load($xml_file);

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
     * @param DOMDocument $dom_document
     *
     * @throws Error | DOMException
     */
    protected function validateXml(DOMDocument $dom_document)
    {
        $schema_path = $this->getOption('schema', $this->getSchemaPath());
        $user_error_handling = $this->enableErrorHandling();

        if (!$dom_document->schemaValidate($schema_path)) {
            throw new Error("The given xml file does not validate against the given schema.");
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
}
