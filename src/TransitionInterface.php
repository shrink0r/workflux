<?php

namespace Workflux;

interface TransitionInterface
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return boolean
     */
    public function isActivatedBy(InputInterface $input, OutputInterface $output);

    /**
     * @return string
     */
    public function getIn();

    /**
     * @return string
     */
    public function getOut();
}
