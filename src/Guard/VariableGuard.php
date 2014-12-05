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
        $parameters = $execution_context->getParameters();

        if (is_object($parameters) && is_callable(array($parameters, 'toArray'))) {
            $params = $parameters->toArray();
        } elseif (is_array($parameters)) {
            $params = $parameters;
        } else {
            throw new RuntimeError('Invalid return type given by execution context get parameters method.');
        }

        return (bool)$this->expression_language->evaluate(
            $this->getOption('expression'),
            array_merge([ 'subject' => $subject ], $params)
        );
    }
}
