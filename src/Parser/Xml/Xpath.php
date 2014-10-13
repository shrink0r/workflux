<?php

namespace Workflux\Parser\Xml;

use DOMDocument;
use DOMXpath;
use DOMNode;

/**
 * The Xpath class is a conveniece wrapper around DOMXpath and simple adds a namespace prefix to queries.
 */
class Xpath extends DOMXpath
{
    /**
     * @var string $namespace_prefix
     */
    protected $namespace_prefix;

    /**
     * Creates a new xpath instance that will use the given 'namespace_prefix' when querying the given document.
     *
     * @param DOMDocument $document
     * @param string $namespace_prefix
     */
    public function __construct(DOMDocument $document, $namespace_prefix)
    {
        parent::__construct($document);

        $this->namespace_prefix = $namespace_prefix;

        $this->registerNamespace(
            $this->namespace_prefix,
            $document->lookupNamespaceUri($document->namespaceURI)
        );
    }

    /**
     * Takes an xpath expression and preprends the parser's namespace prefix to each xpath segment.
     * Then it runs the namespaced expression and returns the result.
     * Example: '//state_machines/state_machine' - expands to -> '//wf:state_machines/wf:state_machine'
     *
     * @param string $expression Non namespaced xpath expression.
     * @param DOMNode $context Allows to pass a context node that is used for the actual xpath query.
     *
     * @return DOMNodeList
     */
    public function query($expression, DOMNode $context = null, $register_ns = null)
    {
        $search = [ '~/(\w+)~', '~^(\w+)$~' ];
        $replace = [ sprintf('/%s:$1', $this->namespace_prefix), sprintf('%s:$1', $this->namespace_prefix) ];
        $namespaced_expression = preg_replace($search, $replace, $expression);

        return parent::query($namespaced_expression, $context, $register_ns);
    }
}
