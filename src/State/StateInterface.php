<?php

namespace Workflux\State;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;

interface StateInterface
{
    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public function execute(InputInterface $input): OutputInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return bool
     */
    public function isInitial(): bool;

    /**
     * @return bool
     */
    public function isFinal(): bool;

    /**
     * @return bool
     */
    public function isBreakpoint(): bool;
}
