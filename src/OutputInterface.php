<?php

namespace Workflux;

interface OutputInterface extends ParamBagInterface
{
    /**
     * @param string $current_state
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public static function fromInput($current_state, InputInterface $input);

    /**
     * @return string
     */
    public function getCurrentState();
}
