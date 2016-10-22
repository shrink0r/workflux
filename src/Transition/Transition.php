<?php

namespace Workflux\Transition;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\Transition\TransitionInterface;

final class Transition implements TransitionInterface
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $label;

    /**
     * @param string $from
     * @param string $to
     * @param string $label
     * @param string $constraints
     */
    public function __construct(string $from, string $to, array $constraints = [], string $label = '')
    {
        $this->from = $from;
        $this->to = $to;
        $this->label = $label;
        $this->constraints = $constraints;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     *
     * @return bool
     */
    public function isActivatedBy(InputInterface $input, OutputInterface $output): bool
    {
        return true;
    }

    public function __toString()
    {
        $label = implode(' and ', $this->constraints);

        return empty($label) ? $this->getLabel() : $label;
    }
}
