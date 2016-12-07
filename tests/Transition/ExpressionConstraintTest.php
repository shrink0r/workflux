<?php

namespace Workflux\Tests\Transition;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Workflux\Param\Input;
use Workflux\Param\Output;
use Workflux\Tests\TestCase;
use Workflux\Transition\ExpressionConstraint;

final class ExpressionConstraintTest extends TestCase
{
    public function testToString()
    {
        $constraint = new ExpressionConstraint("input.get('foo')", new ExpressionLanguage);
        $this->assertEquals("input.get('foo')", (string)$constraint);
    }

    public function testAccepts()
    {
        $input = new Input([ 'foo' => 'bar' ]);
        $output = new Output('initial', [ 'foo' => 'baz' ]);
        $constraint = new ExpressionConstraint("input.get('foo') == 'bar'", new ExpressionLanguage);
        $this->assertTrue($constraint->accepts($input, $output));
    }
}
