<?php

namespace Workflux\Param;

use Workflux\Param\InputInterface;
use Workflux\Param\ParamHolderInterface;

interface OutputInterface extends ParamHolderInterface
{
    /**
     * @param string $current_state
     * @param InputInterface $input
     *
     * @return self
     */
    public static function fromInput(string $current_state, InputInterface $input): self;

    /**
     * @return string
     */
    public function getCurrentState(): string;
}
