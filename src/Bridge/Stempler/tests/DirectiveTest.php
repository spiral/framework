<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Testing\Attribute\TestScope;
use Spiral\Views\Exception\CompileException;
use Spiral\Views\Exception\RenderException;
use Spiral\Views\ViewContext;

class DirectiveTest extends BaseTestCase
{
    public function testRenderDirectiveEx(): void
    {
        $this->expectException(RenderException::class);

        $s = $this->getStempler();

        $s->get('directive', new ViewContext())
            ->render()
        ;
    }

    #[TestScope("http")]
    public function testRenderDirective(): void
    {
        $s = $this->getStempler();
        $this->container->bind(TestInjection::class, new TestInjection('abc'));

        self::assertSame('abc', $s->get('directive', new ViewContext())->render());
    }

    #[TestScope("http")]
    public function testRenderDirectiveAsArray(): void
    {
        $s = $this->getStempler();
        $this->container->bind(TestInjection::class, new TestInjection('abc'));

        self::assertSame('abc', $s->get('directive2', new ViewContext())->render());
    }

    public function testBadDirective(): void
    {
        $this->expectException(CompileException::class);

        $s = $this->getStempler();
        $this->container->bind(TestInjection::class, new TestInjection('abc'));

        $s->get('bad-directive', new ViewContext())
            ->render()
        ;
    }

    public function testRouteDirective(): void
    {
        $s = $this->getStempler()
            ->getBuilder(new ViewContext())
        ;
        self::assertSame("<?php echo \$this->container->get(\Spiral\Stempler\Directive\RouteDirective::class)"
        . "->uri('home', ['action' => 'index']); ?>", $s->compile('route')
            ->getContent());
    }
}
