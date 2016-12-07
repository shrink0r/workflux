<?php

namespace Workflux\Tests\Fixture;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\Transition\TransitionInterface;

/**
 * @codeCoverageIgnore
 */
final class InactiveTransition implements TransitionInterface
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
        return 'inactive';
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     *
     * @return bool
     */
    public function isActivatedBy(InputInterface $input, OutputInterface $output): bool
    {
        return false;
    }

    public function getConstraints(): array
    {
        return [];
    }

    public function hasConstraints(): bool
    {
        return false;
    }
}
