<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Views\ViewContext;

class DirectiveTest extends BaseTest
{
    /**
     * @expectedException \Spiral\Views\Exception\RenderException
     */
    public function testRenderDirectiveEx(): void
    {
        $s = $this->getStempler();

        $s->get('directive', new ViewContext())->render();
    }

    public function testRenderDirective(): void
    {
        $s = $this->getStempler();
        $this->container->bind(TestInjection::class, new TestInjection('abc'));

        $this->assertSame('abc', $s->get('directive', new ViewContext())->render());
    }

    public function testRenderDirectiveAsArray(): void
    {
        $s = $this->getStempler();
        $this->container->bind(TestInjection::class, new TestInjection('abc'));

        $this->assertSame('abc', $s->get('directive2', new ViewContext())->render());
    }

    /**
     * @expectedException \Spiral\Views\Exception\CompileException
     */
    public function testBadDirective(): void
    {
        $s = $this->getStempler();
        $this->container->bind(TestInjection::class, new TestInjection('abc'));

        $s->get('bad-directive', new ViewContext())->render();
    }

    public function testRouteDirective(): void
    {
        $s = $this->getStempler()->getBuilder(new ViewContext());
        $this->assertSame(
            "<?php echo \$this->container->get(\Spiral\Stempler\Directive\RouteDirective::class)"
            . "->uri('home', ['action' => 'index']); ?>",
            $s->compile('route')->getContent()
        );
    }
}
