<?php

namespace Workflux\Error;

use Ds\Vector;
use RuntimeException;
use Shrink0r\SuffixTree\Builder\SuffixTreeBuilder;
use Workflux\Error\WorkfluxError;
use Workflux\State\ExecTracker;

class CorruptExecutionFlow extends RuntimeException implements WorkfluxError
{
    /**
     * @param ExecTracker $exec_tracker
     * @param int $max_cycles
     *
     * @return self
     */
    public static function fromExecTracker(ExecTracker $exec_tracker, int $max_cycles): self
    {
        $cycle_crumbs = $exec_tracker->detectExecutionLoop();
        $message = sprintf("Trying to execute more than the allowed number of %d workflow steps.\n", $max_cycles);
        if (count($cycle_crumbs) === count($exec_tracker->getBreadcrumbs())) {
            $message .= "It is likely that an intentional cycle inside the workflow isn't properly exiting.\n" .
                "The executed states where:\n";
        } else {
            $message .= "Looks like there is a loop between: ";
        }
        $message .= implode(' -> ', $cycle_crumbs->toArray());
        return new self($message);
    }
}
