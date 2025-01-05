<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\Finalizer\TrimRaw;

class TrimRawTest extends BaseTestCase
{
    public function testNotEmpty(): void
    {
        $doc = $this->parse('<a>hello world</a>abc');

        self::assertInstanceOf(Raw::class, $doc->nodes[1]);
    }

    public function testEmpty(): void
    {
        $doc = $this->parse('
            <a>hello world</a>
        ');

        self::assertCount(1, $doc->nodes);
    }

    public function testKeepAttribute(): void
    {
        $doc = $this->parse('
            <a href=" ${name} ${other} ">hello world</a>
        ');

        self::assertCount(1, $doc->nodes);

        /** @var Attr $href */
        $href = $doc->nodes[0]->attrs[0];
        self::assertCount(5, $href->value->nodes);
    }

    protected function getVisitors(): array
    {
        return [new TrimRaw()];
    }
}
