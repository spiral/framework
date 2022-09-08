<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Delete;

class DeleteTest extends BaseTest
{
    public function testPatch(): void
    {
        $cf = $this->getFactory();

        $this->assertEquals(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('.', 'other', ['a' => 'b']));
        $cf->modify('scope', new Delete('.', 'value'));

        $this->assertSame([
            'other' => ['a' => 'b']
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('.', null, 'c'));

        $this->assertSame([
            'other' => ['a' => 'b'],
            'c'
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('.', null, 'c'));

        $this->assertSame([
            'other' => ['a' => 'b']
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('other', 'a'));
        $this->assertSame([
            'other' => []
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Append('.', 'other', ['a' => 'b']));
        $this->assertSame([
            'other' => ['a' => 'b']
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('other', null, 'b'));
        $this->assertSame([
            'other' => []
        ], $cf->getConfig('scope'));
    }

    public function testException(): void
    {
        $cf = $this->getFactory();
        $this->assertEquals(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Delete('something.', 'other'));
        $this->assertEquals(['value' => 'value!'], $cf->getConfig('scope'));
    }
}
