<?php

namespace Workflux\Guard;

use Workflux\IStatefulSubject;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionGuard extends ConfigurableGuard
{
    protected $expression_language;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->expression_language = new ExpressionLanguage();
    }

    public function accept(IStatefulSubject $subject)
    {
        $execution_state = $subject->getExecutionState();

        return $this->expression_language->evaluate(
            $this->getOption('expression'),
            [ 'subject' => $subject, 'params' => $execution_state->getParameters() ]
        );
    }
}
