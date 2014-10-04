<?php

namespace Workflux\Renderer;

use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;

abstract class AbstractRenderer implements IGraphRenderer
{
    use ImmutableOptionsTrait;

    public function __construct(array $options = [])
    {
        $this->options = new ImmutableOptions($options);
    }
}
