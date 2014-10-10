<?php

namespace Workflux\Builder;

/**
 * VerificationInterface implementations are supposed to execute a specific verfication strategy
 * in the context of assuring state machine validity.
 */
interface VerificationInterface
{
    /**
     * Performs a specific verfication.
     *
     * @throws Workflux\Error\VerificationError
     */
    public function verify();
}
