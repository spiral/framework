<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Exception\PatchException;
use Spiral\Config\Patch\Append;

class AppendTest extends BaseTestCase
{
    public function testPatch(): void
    {
        $cf = $this->getFactory();

        self::assertSame(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('.', 'other', ['a' => 'b']));

        self::assertSame([
            'value' => 'value!',
            'other' => ['a' => 'b'],
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('other.', null, 'c'));

        self::assertSame([
            'value' => 'value!',
            'other' => [
                'a' => 'b',
                'c',
            ],
        ], $cf->getConfig('scope'));
    }

    public function testException(): void
    {
        $this->expectException(PatchException::class);

        $cf = $this->getFactory();
        $config = $cf->getConfig('scope');
        self::assertSame(['value' => 'value!'], $config);

        $cf->modify('scope', new Append('other', 'other', ['a' => 'b']));
    }
}
