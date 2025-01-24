<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Delete;

class DeleteTest extends BaseTestCase
{
    public function testPatch(): void
    {
        $cf = $this->getFactory();

        self::assertSame(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('.', 'other', ['a' => 'b']));
        $cf->modify('scope', new Delete('.', 'value'));

        self::assertSame([
            'other' => ['a' => 'b'],
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('.', null, 'c'));

        self::assertSame([
            'other' => ['a' => 'b'],
            'c',
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('.', null, 'c'));

        self::assertSame([
            'other' => ['a' => 'b'],
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('other', 'a'));
        self::assertSame([
            'other' => [],
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('.', 'other', ['a' => 'b']));
        self::assertSame([
            'other' => ['a' => 'b'],
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('other', null, 'b'));
        self::assertSame([
            'other' => [],
        ], $cf->getConfig('scope'));
    }

    public function testException(): void
    {
        $cf = $this->getFactory();
        self::assertSame(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('something.', 'other'));
        self::assertSame(['value' => 'value!'], $cf->getConfig('scope'));
    }
}
