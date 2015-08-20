<?php

namespace Workflux\Guard;

use Workflux\StatefulSubjectInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * The ExpressionGuard employs it's verfification based on the evaluation of a given (symfony) expression.
 * The following variables are available within the configured expression:
 * "subject" - The StatefulSubjectInterface that is being accepted/rejected.
 * "params"  - The parameters array of the subject's execution context.
 */
class ExpressionGuard extends ConfigurableGuard
{
    /**
     * @var ExpressionLanguage $expression_language
     */
    protected $expression_language;

    /**
     * Creates a new ExpressionGuard instance based on with the given options.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->expression_language = new ExpressionLanguage();
    }

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
            [ 'subject' => $subject, 'params' => $execution_context->getParameters() ]
        );
    }

    /**
     * Returns a string represenation of the guard.
     *
     * @return string
     */
    public function __toString()
    {
        return "\nif " . preg_replace('/\s(and|or)/', "\n$1", $this->getOption('expression'));
    }
}
