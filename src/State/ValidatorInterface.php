<?php

namespace Workflux\State;

use Shrink0r\PhpSchema\SchemaInterface;
use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\State\StateInterface;

interface ValidatorInterface
{
    /**
     * @param  StateInterface $state
     * @param  InputInterface $input
     */
    public function validateInput(StateInterface $state, InputInterface $input);

    /**
     * @param  StateInterface $state
     * @param  OutputInterface $output
     */
    public function validateOutput(StateInterface $state, OutputInterface $output);

    /**
     * @return SchemaInterface
     */
    public function getInputSchema(): SchemaInterface;

    /**
     * @return SchemaInterface
     */
    public function getOutputSchema(): SchemaInterface;
}
