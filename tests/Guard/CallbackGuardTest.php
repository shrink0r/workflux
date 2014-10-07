<?php

namespace Workflux\Tests\Guard;

use Workflux\Tests\BaseTestCase;
use Workflux\Guard\CallbackGuard;
use Workflux\Tests\Fixture\GenericSubject;

class CallbackGuardTest extends BaseTestCase
{
    public function testGuard()
    {
        $subject = new GenericSubject('test_machine', 'state1');

        $guard = new CallbackGuard(
            function () {
                return true;
            }
        );

        $this->assertTrue($guard->accept($subject));
        $this->assertEquals("\nif callback is true" , $guard->__toString());
    }
}
