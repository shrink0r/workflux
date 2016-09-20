<?php

namespace Workflux;

interface InputInterface extends ParamBagInterface
{
    /**
     * @param OutputInterface $output
     *
     * @return InputInterface
     */
    public static function fromOutput(OutputInterface $input);
}
