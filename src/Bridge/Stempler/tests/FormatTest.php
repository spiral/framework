<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Views\ViewContext;

class FormatTest extends BaseTestCase
{
    public function testFormatDiv(): void
    {
        $s = $this->getStempler();

        $this->assertSame(
            "<div>\n  hello\n</div>",
            $s->get('format/f1', new ViewContext())->render([])
        );
    }

    public function testFormatDiv2(): void
    {
        $s = $this->getStempler();

        $this->assertSame(
            "<div>\n  hello\n</div>",
            $s->get('format/f2', new ViewContext())->render([])
        );
    }

    public function testFormatDiv3(): void
    {
        $s = $this->getStempler();

        $this->assertSame(
            '<div> first
  <div>
    hello
  </div>
  test
</div>',
            $s->get('format/f3', new ViewContext())->render([])
        );
    }

    public function testFormatDiv4(): void
    {
        $s = $this->getStempler();

        $this->assertSame(
            '<div>
  hello
  <pre>
          test magic


    </pre>
  extra spaces
</div>',
            str_replace("\r", '', $s->get('format/f4', new ViewContext())->render([]))
        );
    }

    public function testFormatDiv5(): void
    {
        $s = $this->getStempler();

        $this->assertSame(
            '<div>
  hello
</div>',
            $s->get('format/f5', new ViewContext())->render([])
        );
    }
}
