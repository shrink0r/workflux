<?php

namespace Workflux\State;

use Shrink0r\PhpSchema\Error;
use Shrink0r\PhpSchema\SchemaInterface;
use Workflux\Error\InvalidInput;
use Workflux\Error\InvalidOutput;
use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\State\StateInterface;

final class Validator implements ValidatorInterface
{
    private $input_schema;

    private $output_schema;

    public function __construct(SchemaInterface $input_schema, SchemaInterface $output_schema)
    {
        $this->input_schema = $input_schema;
        $this->output_schema = $output_schema;
    }

    public function validateInput(StateInterface $state, InputInterface $input)
    {
        $result = $this->input_schema->validate($input->toArray());
        if ($result instanceof Error) {
            throw new InvalidInput(
                $result->unwrap(),
                sprintf("Trying to execute state '%s' with invalid input.", $state->getName())
            );
        }
    }

    public function validateOutput(StateInterface $state, OutputInterface $output)
    {
        $result = $this->output_schema->validate($output->toArray()['params']);
        if ($result instanceof Error) {
            throw new InvalidOutput(
                $result->unwrap(),
                sprintf("Trying to return invalid output from state: '%s'", $state->getName())
            );
        }
    }

    public function getInputSchema(): SchemaInterface
    {
        return $this->input_schema;
    }

    public function getOutputSchema(): SchemaInterface
    {
        return $this->output_schema;
    }
}
