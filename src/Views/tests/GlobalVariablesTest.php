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

        self::assertSame('bar', $variables->getAll()['foo']);
        self::assertArrayNotHasKey('baf', $variables->getAll());

        $variables->set('baz', 'baf');

        self::assertSame('baf', $variables->getAll()['baz']);
    }
}
