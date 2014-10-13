<?php

namespace Workflux\Renderer;

use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;

/**
 * The AbstractRenderer is a base class,
 * that allows you to implement configurable GraphRendererInterface implementations.
 */
abstract class AbstractRenderer implements GraphRendererInterface
{
    use ImmutableOptionsTrait;

    /**
     * Creates a new AbstractRenderer instance, thereby setting the given options.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = new ImmutableOptions($options);
    }
}
