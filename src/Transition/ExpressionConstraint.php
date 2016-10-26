<?php

namespace Workflux\Transition;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;

final class ExpressionConstraint implements ConstraintInterface
{
    /**
     * @var string $expression
     */
    private $expression;

    /**
     * @var ExpressionLanguage $engine
     */
    private $engine;

    public function __construct(string $expression, ExpressionLanguage $engine)
    {
        $this->expression = $expression;
        $this->engine = $engine;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function accepts(InputInterface $input, OutputInterface $output): bool
    {
        return (bool)$this->engine->evaluate($this->expression, [ 'input' => $input, 'output' => $output ]);
    }
}
