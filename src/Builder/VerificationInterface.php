<?php

namespace Workflux\Builder;

interface VerificationInterface
{
    /**
     * @throws Workflux\Error\VerificationError
     */
    public function verify();
}
