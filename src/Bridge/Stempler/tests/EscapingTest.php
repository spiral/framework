<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Views\ViewContext;

/**
 * Output escaping depends on where the value lands in the final template, after imports and extends are merged.
 */
#[TestScope("http")]
final class EscapingTest extends BaseTestCase
{
    private const PAYLOAD = "x');alert(1);('\" onmouseover=alert(2)";
    private const JS_PAYLOAD = '&quot;x\u0027);alert(1);(\u0027\u0022 onmouseover=alert(2)&quot;';

    public static function provideMergedEventHandler(): iterable
    {
        yield 'attribute into an imported on* attribute' => [
            'escape/import-attr',
            '<button onclick="alert(\'' . self::JS_PAYLOAD . '\')">b</button>',
        ];
        yield 'block into an aggregated on* attribute' => [
            'escape/import-block',
            '<button onclick="go(' . self::JS_PAYLOAD . ')">b</button>',
        ];
        yield 'attribute into an extended on* attribute' => [
            'escape/extends',
            '<button onclick="alert(\'' . self::JS_PAYLOAD . '\')">b</button>',
        ];
    }

    #[DataProvider('provideMergedEventHandler')]
    public function testMergedEventHandlerIsEncodedAsJavaScript(string $view, string $expected): void
    {
        $result = $this->getStempler()->get($view, new ViewContext())->render(['value' => self::PAYLOAD]);

        self::assertSame($expected, $result);
    }

    public function testUnquotedAttributeOutputIsQuoted(): void
    {
        $result = $this->getStempler()->get('escape/unquoted', new ViewContext())->render(['value' => self::PAYLOAD]);

        self::assertSame('<a href="x&#039;);alert(1);(&#039;&quot; onmouseover=alert(2)">l</a>', $result);
    }
}
