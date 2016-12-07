<?php

namespace Workflux\Builder;

use Workflux\State\StateInterface;
use Workflux\Transition\TransitionInterface;

interface FactoryInterface
{
    /**
     * @param string $name
     * @param mixed[]|null $state
     *
     * @return StateInterface
     */
    public function createState(string $name, array $state = null): StateInterface;

    /**
     * @param string $from
     * @param string $to
     * @param  mixed[]|null $transition
     *
     * @return TransitionInterface
     */
    public function createTransition(string $from, string $to, array $config = null): TransitionInterface;
}
