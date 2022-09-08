<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use Spiral\Config\Patch\Append;
use Spiral\Config\Patch\Delete;
use Spiral\Config\Patch\Group;
use Spiral\Config\Patch\Prepend;

class GroupTest extends BaseTest
{
    public function testPatch(): void
    {
        $cf = $this->getFactory();
        $this->assertEquals(['value' => 'value!'], $cf->getConfig('scope'));

        $cf->modify('scope', new Group(
            new Prepend('.', 'other', ['a' => 'b']),
            new Delete('other', 'a'),
            new Append('other', 'c', 'd')
        ));

        $this->assertEquals([
            'other' => ['c' => 'd'],
            'value' => 'value!'
        ], $cf->getConfig('scope'));
    }
}
