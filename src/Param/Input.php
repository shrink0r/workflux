<?php

namespace Workflux\Param;

use Workflux\Param\InputInterface;
use Workflux\Param\OutputInterface;
use Workflux\Param\ParamHolderTrait;

final class Input implements InputInterface
{
    use ParamHolderTrait;

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
