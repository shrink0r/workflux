<?php

namespace Workflux;

interface StateInterface
{
    /**
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
     public function execute(InputInterface $input);

    /**
     * @return string
     */
    public function getName();

    public function isInitial();

    public function isFinal();

    public function isBreakpoint();
}
