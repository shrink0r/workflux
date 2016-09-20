<?php

namespace Workflux;

class Output implements OutputInterface
{
    use ParamBagTrait;

    /**
     * @param string $current_state
     */
    private $current_state;

    /**
     * @param mixed[] $params
     */
    public function __construct($current_state, array $params = [])
    {
        $this->current_state = $current_state;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getCurrentState()
    {
        return $this->current_state;
    }

    public function withCurrentState($current_state)
    {
        $output = clone $this;
        $output->current_state = $current_state;

        return $output;
    }

    /**
     * @param string $current_state
     * @param InputInterface $input
     *
     * @return OutputInterface
     */
    public static function fromInput($current_state, InputInterface $input)
    {
        return new static($current_state, $input->toArray());
    }

    public function toArray()
    {
        return [ 'params' => $this->params, 'current_state' => $this->current_state ];
    }
}
