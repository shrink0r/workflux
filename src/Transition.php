<?php

namespace Workflux;

class Transition implements TransitionInterface
{
    private $in;

    private $out;

    public function __construct($in, $out)
    {
        $this->in = $in;
        $this->out = $out;
    }

    public function getIn()
    {
        return $this->in;
    }

    public function getOut()
    {
        return $this->out;
    }

    public function isActivatedBy(InputInterface $input, OutputInterface $output)
    {
        return true;
    }
}
