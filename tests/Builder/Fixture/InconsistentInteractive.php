<?php

namespace Workflux\Tests\Builder\Fixture;

use Workflux\State\StateInterface;
use Workflux\State\StateTrait;

final class InconsistentInteractive implements StateInterface
{
    use StateTrait;

    public function isInteractive(): bool
    {
        return false;
    }
}
