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
}
