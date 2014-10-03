<?php

namespace Workflux\Builder;

interface IVerification
{
    /**
     * @throws Workflux\Error\VerificationError
     */
    public function verify();
}
