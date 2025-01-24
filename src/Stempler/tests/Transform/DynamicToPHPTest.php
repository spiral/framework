<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;

class DynamicToPHPTest extends BaseTestCase
{
    public static function provideStringWithoutDirective(): iterable
    {
        yield ['https://unpkg.com/tailwindcss@^1.6/dist/tailwind.min.css'];
    }

    public function testOutput(): void
    {
        $doc = $this->parse('{{ $name }}');

        self::assertInstanceOf(PHP::class, $doc->nodes[0]);
    }

    #[DataProvider('provideStringWithoutDirective')]
    public function testLinkWithReservedSymbol(string $string): void
    {
        $doc = $this->parse($string);

        self::assertInstanceOf(Raw::class, $doc->nodes[0]);
        self::assertSame($string, $doc->nodes[0]->content);
    }

    public function testDirective(): void
    {
        $doc = $this->parse('@foreach($users as $u) @endforeach');

        self::assertInstanceOf(PHP::class, $doc->nodes[0]);
        self::assertInstanceOf(PHP::class, $doc->nodes[2]);
    }

    public function testContextAwareEscapeSimpleEcho(): void
    {
        self::assertSame('<?php echo htmlspecialchars((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>', $res = $this->compile('{{ "hello world" }}')->getContent());

        self::assertSame('hello world', $this->eval($res));
    }

    public function testContextAwareEscapeAttribute(): void
    {
        self::assertSame('<a href="<?php echo htmlspecialchars'
        . '((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>"></a>', $res = $this->compile('<a href="{{ "hello world" }}"></a>')->getContent());

        self::assertSame('<a href="hello world"></a>', $this->eval($res));
    }

    public function testVerbatim(): void
    {
        self::assertSame('<a style="color: <?php echo htmlspecialchars'
        . '((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>"></a>', $res = $this->compile('<a style="color: {{ "hello world" }}"></a>')->getContent());

        self::assertSame('<a style="color: hello world"></a>', $this->eval($res));
    }

    public function testVerbatim2(): void
    {
        self::assertSame('<a onclick="alert(<?php echo \'&quot;\', '
        . 'htmlspecialchars((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'), \'&quot;\'; ?>)"></a>', $res = $this->compile('<a onclick="alert({{ "hello world" }})"></a>')->getContent());

        self::assertSame('<a onclick="alert(&quot;hello world&quot;)"></a>', $this->eval($res));
    }

    public function testVerbatim3(): void
    {
        self::assertSame('<script>alert(<?php echo json_encode'
        . '("hello world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, 512); ?>)</script>', $res = $this->compile('<script>alert({{ "hello world" }})</script>')->getContent());

        self::assertSame('<script>alert("hello world")</script>', $this->eval($res));
    }

    public function testVerbatim4(): void
    {
        self::assertSame('<script>alert(<?php echo json_encode' .
        '("hello\' \'world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, 512); ?>)</script>', $res = $this->compile('<script>alert({{ "hello\' \'world" }})</script>')->getContent());

        self::assertSame('<script>alert("hello\u0027 \u0027world")</script>', $this->eval($res));
    }

    protected function getVisitors(): array
    {
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());

        return [$dynamic];
    }

    private function eval(string $body): string
    {
        ob_start();

        eval('?>' . $body);

        return ob_get_clean();
    }
}
