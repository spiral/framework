<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Directive;

use Spiral\Tests\Stempler\fixtures\ImageDirective;

final class DirectiveTest extends BaseTestCase
{
    protected const DIRECTIVES = [
        ImageDirective::class,
    ];

    public function testStringWithZeroChars(): void
    {
        $doc = $this->parse('@image("blog", "test.png", "150|250", "webp")');

        $this->assertSame(
            '<img title="blog" src="test.png" size="150|250" type="webp">',
            $this->compile($doc),
        );
    }

    public function testStringWithSingleQuotes(): void
    {
        $doc = $this->parse("@image('blog', 'test.png', '150|250', 'webp')");

        $this->assertSame(
            "<img title='blog' src='test.png' size='150|250' type='webp'>",
            $this->compile($doc),
        );
    }

    public function testVariableInjection(): void
    {
        $doc = $this->parse('@image("blog", $src, "150|250", "webp")');

        $this->assertSame(
            '<img title="blog" src="<?php echo $src; ?>" size="150|250" type="webp">',
            $this->compile($doc),
        );
    }
}
