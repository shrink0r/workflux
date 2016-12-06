<?php

namespace Workflux\Param;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;

interface InputInterface extends ParamHolderInterface
{
    /**
     * @param OutputInterface $output
     *
     * @return InputInterface
     */
    public static function fromOutput(OutputInterface $input): InputInterface;

    /**
     * @return string
     */
    public function getEvent(): string;

    /**
     * @return boolean
     */
    public function hasEvent(): bool;

    /**
     * @param  string $event
     * @return InputInterface
     */
    public function withEvent(string $event): InputInterface;
}
