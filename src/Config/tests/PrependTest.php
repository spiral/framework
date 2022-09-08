<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Exception\PatchException;
use Spiral\Config\Patch\Prepend;

class PrependTest extends BaseTest
{
    public function testPatch(): void
    {
        $cf = $this->getFactory();

        $this->assertEquals(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Prepend('.', 'other', ['a' => 'b']));

        $this->assertSame([
            'other' => ['a' => 'b'],
            'value' => 'value!',
        ], $cf->getConfig('scope'));

        $cf->modify('scope', new Prepend('other.', null, 'c'));

        $this->assertSame([
            'other' => [
                'c',
                'a' => 'b',
            ],
            'value' => 'value!',
        ], $cf->getConfig('scope'));
    }

    public function testException(): void
    {
        $this->expectException(PatchException::class);

        $cf = $this->getFactory();
        $config = $cf->getConfig('scope');
        $this->assertEquals(['value' => 'value!'], $config);

        $cf->modify('scope', new Prepend('other', 'other', ['a' => 'b']));
    }
}
