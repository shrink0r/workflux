<?php

namespace Workflux\Param;

use Workflux\Param\ParamHolderTrait;

final class Settings implements ParamHolderInterface
{
    use ParamHolderTrait;

    /**
     * @param mixed[] $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }
}
