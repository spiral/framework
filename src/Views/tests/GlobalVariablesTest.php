<?php

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Views\GlobalVariables;

final class GlobalVariablesTest extends TestCase
{
    public function testSets(): void
    {
        $variables = new GlobalVariables([
            'foo' => 'bar'
        ]);

        $this->assertSame('bar', $variables->getAll()['foo']);
        $this->assertArrayNotHasKey('baf', $variables->getAll());

        $variables->set('baz', 'baf');

        $this->assertSame('baf', $variables->getAll()['baz']);
    }
}
