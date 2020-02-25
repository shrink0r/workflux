<?php

namespace Workflux\Guard;

use Workflux\StatefulSubjectInterface;
use Workflux\Error\Error;

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
            throw new Error('Invalid return type given by execution context get parameters method.');
        }

        $expression = $this->getOption('expression');
        $params = array_merge([ 'subject' => $subject ], $params);

        $result = null;
        try {
            $result = (bool)$this->expression_language->evaluate($expression, $params);
        } catch (\Exception $exc) {
            $vars = '';
            foreach ($params as $var => $val) {
                $vars .= $var . '('.gettype($val).') ';
            }

            throw new Error(
                "Expression evaluation failed. Reason: " . $exc->getMessage() .
                "\nExpression used: " . $expression .
                "\nExpression vars: " . $vars,
                1,
                $exc
            );
        }

        return $result;
    }
}
