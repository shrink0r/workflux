<?php

namespace Workflux\Tests\Builder\Fixture;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\Transition\TransitionInterface;

class AlwaysTrueTransition implements TransitionInterface
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
     * @param string $from
     * @param string $to
     */
    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
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
        return 'always-true';
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

    public function getConstraints(): array
    {
        return [];
    }
}
