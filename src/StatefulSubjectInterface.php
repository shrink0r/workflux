<?php

namespace Workflux;

/**
 * StatefulSubjectInterface provides the main contract between any external objects and the workflux statemachine.
 * The subject is always passed to the traversal callbacks
 * and it's execution context represents the current traversal state.
 */
interface StatefulSubjectInterface
{
    /**
     * Returns the subject's execution context.
     *
     * @return ExecutionContextInterface
     */
    public function getExecutionContext();
}
