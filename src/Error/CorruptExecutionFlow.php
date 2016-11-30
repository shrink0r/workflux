<?php

namespace Workflux\Error;

use Ds\Vector;
use Shrink0r\SuffixTree\Builder\SuffixTreeBuilder;
use Workflux\Error\WorkfluxError;

class CorruptExecutionFlow extends WorkfluxError
{
    public static function raiseLoopDetected(Vector $bread_crumbs, int $max_depth): self
    {
        throw new self(self::buildMessage($bread_crumbs, $max_depth));
    }

    /**
     * @param Vector $bread_crumbs
     */
    private static function buildMessage(Vector $bread_crumbs, int $max_depth): string
    {
        $message = sprintf("Trying to execute more than the allowed number of %d workflow steps.\n", $max_depth);
        $bread_crumbs = self::detectExecutionLoop($bread_crumbs);
        if (count($bread_crumbs) === $bread_crumbs) {
            $message .= "It is likely that an intentional cycle inside the workflow isn't properly exiting.\n" .
                "The executed states where:\n";
        } else {
            $message .= "Looks like there is a loop between: ";
        }
        $message .= implode(' -> ', $bread_crumbs->toArray());
        return $message;
    }

    /**
     * @param Vector $bread_crumbs
     *
     * @return Vector
     */
    private static function detectExecutionLoop(Vector $bread_crumbs): Vector
    {
        $execution_path = implode(' ', $bread_crumbs->toArray());
        $tree_builder = new SuffixTreeBuilder;
        $loop_path = $execution_path;
        do {
            $possible_loop_path = $loop_path;
            $suffix_tree = $tree_builder->build($loop_path.'$');
            $loop_path = trim($suffix_tree->findLongestRepetition());
        } while (strlen($loop_path) > 2);

        if ($possible_loop_path !== $execution_path) {
            return new Vector(explode(' ', $possible_loop_path));
        }
        return $bread_crumbs;
    }
}
