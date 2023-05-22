<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Node\PHP;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;

class DynamicToPHPTest extends BaseTestCase
{
    public function testOutput(): void
    {
        $doc = $this->parse('{{ $name }}');

        $this->assertInstanceOf(PHP::class, $doc->nodes[0]);
    }

    public function testDirective(): void
    {
        $doc = $this->parse('@foreach($users as $u) @endforeach');

        $this->assertInstanceOf(PHP::class, $doc->nodes[0]);
        $this->assertInstanceOf(PHP::class, $doc->nodes[2]);
    }

    public function testContextAwareEscapeSimpleEcho(): void
    {
        $this->assertSame(
            '<?php echo htmlspecialchars((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>',
            $res = $this->compile('{{ "hello world" }}')->getContent()
        );

        $this->assertSame(
            'hello world',
            $this->eval($res)
        );
    }

    public function testContextAwareEscapeAttribute(): void
    {
        $this->assertSame(
            '<a href="<?php echo htmlspecialchars'
            . '((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>"></a>',
            $res = $this->compile('<a href="{{ "hello world" }}"></a>')->getContent()
        );

        $this->assertSame(
            '<a href="hello world"></a>',
            $this->eval($res)
        );
    }

    public function testVerbatim(): void
    {
        $this->assertSame(
            '<a style="color: <?php echo htmlspecialchars'
            . '((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>"></a>',
            $res = $this->compile('<a style="color: {{ "hello world" }}"></a>')->getContent()
        );

        $this->assertSame(
            '<a style="color: hello world"></a>',
            $this->eval($res)
        );
    }

    public function testVerbatim2(): void
    {
        $this->assertSame(
            '<a onclick="alert(<?php echo \'&quot;\', '
            . 'htmlspecialchars((string) ("hello world"), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'), \'&quot;\'; ?>)"></a>',
            $res = $this->compile('<a onclick="alert({{ "hello world" }})"></a>')->getContent()
        );

        $this->assertSame(
            '<a onclick="alert(&quot;hello world&quot;)"></a>',
            $this->eval($res)
        );
    }

    public function testVerbatim3(): void
    {
        $this->assertSame(
            '<script>alert(<?php echo json_encode'
            . '("hello world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, 512); ?>)</script>',
            $res = $this->compile('<script>alert({{ "hello world" }})</script>')->getContent()
        );

        $this->assertSame(
            '<script>alert("hello world")</script>',
            $this->eval($res)
        );
    }

    public function testVerbatim4(): void
    {
        $this->assertSame(
            '<script>alert(<?php echo json_encode' .
            '("hello\' \'world", JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, 512); ?>)</script>',
            $res = $this->compile('<script>alert({{ "hello\' \'world" }})</script>')->getContent()
        );

        $this->assertSame(
            '<script>alert("hello\u0027 \u0027world")</script>',
            $this->eval($res)
        );
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
