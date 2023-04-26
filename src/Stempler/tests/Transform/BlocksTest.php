<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class BlocksTest extends BaseTestCase
{
    public function testDefineBlock(): void
    {
        $doc = $this->parse('<block:world>hello</block:world>');
        $this->assertInstanceOf(Block::class, $doc->nodes[0]);
        $this->assertSame('world', $doc->nodes[0]->name);
        $this->assertSame('hello', $doc->nodes[0]->nodes[0]->content);
    }

    public function testDefineShortBlock(): void
    {
        $doc = $this->parse('<block:world/>');
        $this->assertInstanceOf(Block::class, $doc->nodes[0]);
        $this->assertSame('world', $doc->nodes[0]->name);
        $this->assertSame([], $doc->nodes[0]->nodes);
    }

    protected function getVisitors(): array
    {
        return [new DefineBlocks()];
    }
}
