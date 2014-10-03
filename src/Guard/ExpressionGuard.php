<?php

namespace Workflux\Guard;

use Workflux\IStatefulSubject;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionGuard implements IGuard
{
    protected $expression;

    public function __construct($expression)
    {
        $this->expression = $expression;
        $this->expression_language = new ExpressionLanguage();
    }

    public function accept(IStatefulSubject $subject)
    {
        $execution_state = $subject->getExecutionState();

        return $this->expression_language->evaluate(
            $this->expression,
            [ 'subject' => $subject, 'params' => $execution_state->getParameters() ]
        );
    }
}
