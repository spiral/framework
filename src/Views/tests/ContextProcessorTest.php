<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Views;

use PHPUnit\Framework\TestCase;
use Spiral\Views\Context\ValueDependency;
use Spiral\Views\Exception\ContextException;
use Spiral\Views\Processor\ContextProcessor;
use Spiral\Views\Traits\ProcessorTrait;
use Spiral\Views\ViewContext;
use Spiral\Views\ViewLoader;
use Spiral\Views\ViewSource;

class ContextProcessorTest extends TestCase
{
    use ProcessorTrait;

    public function testProcessContext(): void
    {
        $this->processors[] = new ContextProcessor();

        $source = $this->getSource('other:inject');

        $this->assertSame('hello @{name|default}', $source->getCode());

        $ctx = new ViewContext();
        $source2 = $this->process($source, $ctx->withDependency(new ValueDependency('name', 'Bobby')));
        $this->assertSame('hello Bobby', $source2->getCode());
    }

    public function testProcessContextException(): void
    {
        $this->expectException(ContextException::class);

        $this->processors[] = new ContextProcessor();

        $source = $this->getSource('other:inject');

        $this->assertSame('hello @{name|default}', $source->getCode());

        $ctx = new ViewContext();
        $this->process($source, $ctx);
    }

    protected function getSource(string $path): ViewSource
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other'   => __DIR__ . '/fixtures/other',
        ]);

        return $loader->withExtension('php')->load($path);
    }
}
