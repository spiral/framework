<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;

final class DynamicToPHPTest extends BaseTestCase
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
        self::assertSame('<a onclick="alert(<?php echo htmlspecialchars(json_encode('
        . '"hello world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT '
        . '| JSON_INVALID_UTF8_SUBSTITUTE, 512), '
        . 'ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>)"></a>', $res = $this->compile('<a onclick="alert({{ "hello world" }})"></a>')->getContent());

        self::assertSame('<a onclick="alert(&quot;hello world&quot;)"></a>', $this->eval($res));
    }

    /**
     * An event handler attribute is JavaScript delivered inside HTML: the browser HTML-decodes the value before
     * the JS engine parses it. Quotes therefore have to be JavaScript escapes; HTML entities would decode back
     * into real quotes and end the string literal early.
     */
    public function testVerbatimEventHandlerEscapesQuotesForJavaScript(): void
    {
        // chr(34) keeps the double quote out of the template itself, where it would close the attribute
        $res = $this->compile('<a onclick="alert({{ chr(34) . \'hi\' . chr(34) }})"></a>')->getContent();

        self::assertSame(
            '<a onclick="alert(&quot;\u0022hi\u0022&quot;)"></a>',
            $rendered = $this->eval($res),
        );

        // what the JS engine parses, once the browser has decoded the attribute value
        self::assertSame(
            'alert("\u0022hi\u0022")',
            $this->decodeEventHandler($rendered, 'onclick'),
        );
    }

    public function testVerbatimEventHandlerKeepsNonStringTypes(): void
    {
        $res = $this->compile('<a onclick="alert({{ 123 }})"></a>')->getContent();

        self::assertSame('<a onclick="alert(123)"></a>', $this->eval($res));
    }

    /**
     * Broken input must degrade the way ENT_SUBSTITUTE does, not make json_encode() return false and silently
     * collapse the value to an empty string.
     */
    public function testVerbatimEventHandlerSubstitutesInvalidUtf8(): void
    {
        $res = $this->compile('<a onclick="alert({{ chr(177) . \'1\' }})"></a>')->getContent();

        self::assertSame('<a onclick="alert(&quot;\ufffd1&quot;)"></a>', $this->eval($res));
    }

    public function testVerbatim3(): void
    {
        self::assertSame('<script>alert(<?php echo json_encode'
        . '("hello world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT '
        . '| JSON_INVALID_UTF8_SUBSTITUTE, 512); ?>)</script>', $res = $this->compile('<script>alert({{ "hello world" }})</script>')->getContent());

        self::assertSame('<script>alert("hello world")</script>', $this->eval($res));
    }

    public function testVerbatim4(): void
    {
        self::assertSame('<script>alert(<?php echo json_encode' .
        '("hello\' \'world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT '
        . '| JSON_INVALID_UTF8_SUBSTITUTE, 512); ?>)</script>', $res = $this->compile('<script>alert({{ "hello\' \'world" }})</script>')->getContent());

        self::assertSame('<script>alert("hello\u0027 \u0027world")</script>', $this->eval($res));
    }

    /**
     * Without JSON_INVALID_UTF8_SUBSTITUTE json_encode() returns false on broken input and the value collapses
     * to an empty string, silently changing the arity of the surrounding JavaScript call.
     */
    public function testVerbatimScriptSubstitutesInvalidUtf8(): void
    {
        $res = $this->compile('<script>alert({{ chr(177) . \'1\' }})</script>')->getContent();

        self::assertSame('<script>alert("\ufffd1")</script>', $this->eval($res));
    }

    protected function getVisitors(): array
    {
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());

        return [$dynamic];
    }

    private function eval(string $body): string
    {
        \ob_start();

        eval('?>' . $body);

        return \ob_get_clean();
    }

    /**
     * Emulates the browser: the HTML parser decodes entities in an attribute value, and the JS engine parses
     * the decoded result.
     */
    private function decodeEventHandler(string $html, string $attribute): string
    {
        \preg_match('/' . \preg_quote($attribute, '/') . '="([^"]*)"/', $html, $matches);

        return \html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'utf-8');
    }
}
