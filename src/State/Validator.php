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
    /**
     * @var SchemaInterface $input_schema
     */
    private $input_schema;

    /**
     * @var SchemaInterface $output_schema
     */
    private $output_schema;

    /**
     * @param SchemaInterface $input_schema
     * @param SchemaInterface $output_schema
     */
    public function __construct(SchemaInterface $input_schema, SchemaInterface $output_schema)
    {
        $this->input_schema = $input_schema;
        $this->output_schema = $output_schema;
    }

    /**
     * @param  StateInterface $state
     * @param  InputInterface $input
     */
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

    /**
     * @param  StateInterface $state
     * @param  OutputInterface $output
     */
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

    /**
     * @return SchemaInterface
     */
    public function getInputSchema(): SchemaInterface
    {
        return $this->input_schema;
    }

    /**
     * @return SchemaInterface
     */
    public function getOutputSchema(): SchemaInterface
    {
        return $this->output_schema;
    }
}
