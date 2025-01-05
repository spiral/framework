<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Patch\Set;

class SetTest extends BaseTestCase
{
    public function testPatch(): void
    {
        $cf = $this->getFactory();

        self::assertSame(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Set('value', 'x'));

        self::assertSame([
            'value' => 'x',
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Set('value', 'y'));

        self::assertSame([
            'value' => 'y',
        ], $cf->getConfig('scope'));
    }
}
