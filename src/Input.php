<?php

namespace Workflux;

class Input implements InputInterface
{
    use ParamBagTrait;

    /**
     * @param mixed[] $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * @param OutputInterface $output
     *
     * @return InputInterface
     */
    public static function fromOutput(OutputInterface $output): InputInterface
    {
        $output_arr = $output->toArray();

        return new static($output_arr['params']);
    }
}
