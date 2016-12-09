<?php

namespace Workflux\Transition;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;

interface ConstraintInterface
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function accepts(InputInterface $input, OutputInterface $output): bool;

    /**
     * @return string
     */
    public function __toString(): string;
}
