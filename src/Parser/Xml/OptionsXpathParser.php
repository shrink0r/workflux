<?php

namespace Workflux\Parser\Xml;

use Workflux\Parser\ParserInterface;
use Workflux\Error\Error;
use DOMElement;

/**
 * The OptionsXpathParser can parse 'options' that are defined below a given context node.
 */
class OptionsXpathParser implements ParserInterface
{
    /**
     * @var Xpath $xpath;
     */
    protected $xpath;

    /**
     * Creates a new OptionsXpathParser instance that uses the given xpath.
     *
     * @param Xpath $xpath
     */
    public function __construct(Xpath $xpath)
    {
        $this->xpath = $xpath;
    }

    /**
     * Takes a xml node value and casts it to it's php scalar counterpart.
     *
     * @param string $value
     *
     * @return string | boolean | int
     */
    public static function literalize($value)
    {
        if (preg_match('/^\d+$/', $value)) {
            return (int)$value;
        } else {
            return self::literalizeString($value);
        }
    }

    /**
     * Takes an xml node value and returns it either as a string or boolean.
     *
     * @param string $value Following values are cast to bool true/false: on, yes, true/off, no, false
     *
     * @return string | boolean
     */
    public static function literalizeString($value)
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

    /**
     * Parses all options below the given 'options context' in an array.
     *
     * @param mixed $options_context
     *
     * @return mixed
     */
    public function parse($options_context)
    {
        if (!$options_context instanceof DOMElement) {
            throw new Error(
                sprintf(
                    'Invalid "options_context" argument passed to %s. Only instances of DOMElement supported.',
                    __METHOD__
                )
            );
        }

        return $this->parseOptions($options_context);
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
        $option_index = 0;

        foreach ($this->xpath->query('option', $options_context) as $option_element) {
            $option_key = $this->resolveOptionKey($option_element, $option_index);
            $option_value = $this->resolveOptionValue($option_element);

            $options[$option_key] = $option_value;
            $option_index++;
        }

        return $options;
    }

    /**
     * Returns either a literalized scalar option value or nested options array.
     *
     * @param DOMElement $option_element
     *
     * @return mixed
     */
    protected function resolveOptionValue(DOMElement $option_element)
    {
        $child_options = $this->xpath->query('option', $option_element);
        if ($child_options->length > 0) {
            $option_value = $this->parseOptions($option_element);
        } else {
            $option_value = self::literalize($option_element->nodeValue);
        }

        return $option_value;
    }

    /**
     * Returns the appropiate key to use for indexing the given option to an options array.
     *
     * @param DOMElement $option_element
     * @param int $sibling_count
     *
     * @return string|int Either assoc. string key or numeric index.
     */
    protected function resolveOptionKey(DOMElement $option_element, $sibling_count = 0)
    {
        if ($option_element->hasAttribute('name')) {
            $option_index = $option_element->getAttribute('name');
        } else {
            $option_index = $sibling_count;
        }

        return $option_index;
    }
}
