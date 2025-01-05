<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Testing\Attribute\TestScope;
use Spiral\Views\Context\ValueDependency;
use Spiral\Views\Exception\CompileException;
use Spiral\Views\Exception\RenderException;
use Spiral\Views\ViewContext;

#[TestScope("http")]
class EngineTest extends BaseTestCase
{
    public function testList(): void
    {
        $views = $this->getStempler()->getLoader()->list();

        self::assertContains('default:test', $views);
        self::assertContains('other:test', $views);
    }

    public function testRender(): void
    {
        $s = $this->getStempler();
        self::assertSame('test', $s->get('test', new ViewContext())->render([]));

        self::assertSame('other test', $s->get('other:test', new ViewContext())->render([]));
    }

    public function testRenderInContext(): void
    {
        $ctx = new ViewContext();
        $ctx = $ctx->withDependency(new ValueDependency('name', 'Test'));

        $s = $this->getStempler();

        self::assertSame('hello Anton of Test', $s->get('other:ctx', $ctx)->render(['name' => 'Anton']));
    }

    public function testRenderException(): void
    {
        $s = $this->getStempler();

        try {
            $s->get('echo', new ViewContext())->render();
            $this->fail('Exception expected');
        } catch (RenderException $e) {
            $t = $e->getUserTrace()[0];

            self::assertSame(2, $t['line']);
            self::assertStringContainsString('echo.dark.php', $t['file']);
        }
    }

    public function testRenderNestedException(): void
    {
        $s = $this->getStempler();

        try {
            $s->get('other:echo-in', new ViewContext())->render();
            $this->fail('Exception expected');
        } catch (RenderException $e) {
            $t = $e->getUserTrace();
            self::assertCount(2, $t);

            self::assertSame(2, $t[0]['line']);
            self::assertStringContainsString('echo.dark.php', $t[0]['file']);

            self::assertSame(3, $t[1]['line']);
            self::assertStringContainsString('echo-in.dark.php', $t[1]['file']);
        }
    }

    public function testSyntaxException(): void
    {
        $twig = $this->getStempler();

        try {
            $twig->get('other:bad', new ViewContext());
        } catch (CompileException $e) {
            self::assertStringContainsString('bad.dark.php', $e->getFile());
        }
    }
}
