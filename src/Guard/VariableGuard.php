<?php

namespace Workflux\Guard;

use Workflux\StatefulSubjectInterface;

/**
 * The VariableGuard employs it's verfification based on the evaluation of a given (symfony) expression.
 * It makes all execution context parameters directly addressable within the expression.
 */
class VariableGuard extends ExpressionGuard
{
    /**
     * Evaluates the configured (symfony) expression for the given subject.
     *
     * @param StatefulSubjectInterface $subject
     *
     * @return boolean
     */
    public function accept(StatefulSubjectInterface $subject)
    {
        $execution_context = $subject->getExecutionContext();

        return (bool)$this->expression_language->evaluate(
            $this->getOption('expression'),
            array_merge([ 'subject' => $subject ], $execution_context->getParameters()->toArray())
        );
    }
}
