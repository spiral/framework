<?php

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

final class ContextProcessorTest extends TestCase
{
    use ProcessorTrait;

    public function testProcessContext(): void
    {
        $this->processors[] = new ContextProcessor();
        $source = $this->getSource('other:inject');
        $this->assertSame("hello @{name}\n", $source->getCode());
        $ctx = new ViewContext();
        $source2 = $this->process($source, $ctx->withDependency(new ValueDependency('name', 'Bobby')));
        $this->assertSame("hello Bobby\n", $source2->getCode());
    }

    public function testProcessContextWithDefaultValue(): void
    {
        $this->processors[] = new ContextProcessor();
        $source = $this->getSource('other:injectWithDefault');
        $this->assertSame("hello @{name|default}\n", $source->getCode());
        $ctx = new ViewContext();
        $source2 = $this->process($source, $ctx->withDependency(new ValueDependency('name', 'Bobby')));
        $this->assertSame("hello Bobby\n", $source2->getCode());
    }

    public function testProcessContextShouldUseDefaultValueIfKeyNotFound(): void
    {
        $this->processors[] = new ContextProcessor();
        $source = $this->getSource('other:injectWithDefault');
        $ctx = new ViewContext();
        $source2 = $this->process($source, $ctx);
        $this->assertSame("hello default\n", $source2->getCode());
    }

    public function testProcessContextShouldUseDefaultValueIfContextIsNull(): void
    {
        $this->processors[] = new ContextProcessor();
        $source = $this->getSource('other:injectWithDefault');
        $ctx = new ViewContext();
        $source2 = $this->process($source, $ctx->withDependency(new ValueDependency('name', null)));
        $this->assertSame("hello default\n", $source2->getCode());
    }

    public function testProcessContextException(): void
    {
        $this->expectException(ContextException::class);

        $this->processors[] = new ContextProcessor();

        $source = $this->getSource('other:inject');
        $ctx = new ViewContext();
        $this->process($source, $ctx);
    }

    protected function getSource(string $path): ViewSource
    {
        $loader = new ViewLoader([
            'default' => __DIR__ . '/fixtures/default',
            'other' => __DIR__ . '/fixtures/other',
        ]);

        return $loader->withExtension('php')->load($path);
    }
}
