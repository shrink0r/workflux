<?php

namespace Workflux\Guard;

use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;

abstract class ConfigurableGuard implements IGuard
{
    use ImmutableOptionsTrait;

    public function __construct(array $options = [])
    {
        $this->options = new ImmutableOptions($options);
    }
}
