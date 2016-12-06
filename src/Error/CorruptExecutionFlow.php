<?php

namespace Workflux\Error;

use RuntimeException;
use Workflux\Error\ErrorInterface;
use Workflux\State\ExecutionTracker;

class CorruptExecutionFlow extends RuntimeException implements ErrorInterface
{
    /**
     * @param ExecutionTracker $exec_tracker
     * @param int $max_cycles
     *
     * @return self
     */
    public static function fromExecutionTracker(ExecutionTracker $execution_tracker, int $max_cycles): self
    {
        $cycle_crumbs = $execution_tracker->detectExecutionLoop();
        $message = sprintf("Trying to execute more than the allowed number of %d workflow steps.\n", $max_cycles);
        if (count($cycle_crumbs) === count($execution_tracker->getBreadcrumbs())) {
            $message .= "It is likely that an intentional cycle inside the workflow isn't properly exiting.\n" .
                "The executed states where:\n";
        } else {
            $message .= "Looks like there is a loop between: ";
        }
        $message .= implode(' -> ', $cycle_crumbs->toArray());
        return new self($message);
    }
}
