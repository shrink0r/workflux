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
    public static function fromInput(string $current_state, InputInterface $input): OutputInterface;

    /**
     * @return string
     */
    public function getCurrentState(): string;
}
