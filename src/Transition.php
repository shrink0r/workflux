<?php

namespace Workflux;

class Transition implements TransitionInterface
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

    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param  InputInterface $input
     * @param  OutputInterface $output
     *
     * @return boolean
     */
    public function isActivatedBy(InputInterface $input, OutputInterface $output): bool
    {
        return true;
    }
}
