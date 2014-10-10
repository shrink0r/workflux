<?php

namespace Workflux\Parser;

/**
 * ParserInterface implementations are supposed to parse specific payload and turn it into a common array structure,
 * that is expected by the StateMachineBuilderInterface.
 */
interface ParserInterface
{
    /**
     * Parses the given payload and returns the corresponding data as an array.
     *
     * @param mixed $payload
     *
     * @return array
     */
    public function parse($payload);
}
