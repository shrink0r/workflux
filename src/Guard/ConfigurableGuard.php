<?php

namespace Workflux\Guard;

use Params\Immutable\ImmutableOptionsTrait;
use Params\Immutable\ImmutableOptions;

/**
 * The ConfigurableGuard is a base class that allows you to implement configurable GuardInterface implementations.
 */
abstract class ConfigurableGuard implements GuardInterface
{
    use ImmutableOptionsTrait;

    /**
     * Creates a new ConfigurableGuard instance based on with the given options.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = new ImmutableOptions($options);
    }
}
