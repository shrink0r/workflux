<?php

namespace Workflux\Transition;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;

interface TransitionInterface
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function isActivatedBy(InputInterface $input, OutputInterface $output): bool;

    /**
     * @return string
     */
    public function getFrom(): string;

    /**
     * @return string
     */
    public function getTo(): string;

    /**
     * @return string
     */
    public function getLabel(): string;
}
