<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\InterceptorException;
use Spiral\Core\InterceptableCore;
use Spiral\Core\InterceptorPipeline;
use Spiral\Core\Scope;
use Spiral\Framework\ScopeName;
use Spiral\Tests\Core\Fixtures\DummyController;
use Spiral\Tests\Core\Fixtures\SampleCore;

final class InterceptableCoreTest extends TestCase
{
    private Container $root;

    protected function setUp(): void
    {
        $this->root = new Container();
    }

    public function testNoInterceptors(): void
    {
        $core = new SampleCore($this->root);
        $int = new InterceptableCore($core);

        $this->root->runScope(new Scope(ScopeName::Http), function () use ($int): void {
            $this->assertSame('Hello, Antony.', $int->callAction(
                DummyController::class,
                'index',
                ['name' => 'Antony']
            ));
        });
    }

    public function testNoInterceptors2(): void
    {
        $core = new SampleCore($this->root);
        $int = new InterceptableCore($core);
        $int->addInterceptor(new DemoInterceptor());

        $this->root->runScope(new Scope(ScopeName::Http), function () use ($int): void {
            $this->assertSame('?Hello, Antony.!', $int->callAction(
                DummyController::class,
                'index',
                ['name' => 'Antony']
            ));
        });
    }

    public function testNoInterceptors22(): void
    {
        $core = new SampleCore($this->root);
        $int = new InterceptableCore($core);
        $int->addInterceptor(new DemoInterceptor());
        $int->addInterceptor(new DemoInterceptor());

        $this->root->runScope(new Scope(ScopeName::Http), function () use ($int): void {
            $this->assertSame('??Hello, Antony.!!', $int->callAction(
                DummyController::class,
                'index',
                ['name' => 'Antony']
            ));
        });
    }

    public function testInvalidPipeline(): void
    {
        $this->expectException(InterceptorException::class);

        $pipeline = new InterceptorPipeline();

        $this->root->runScope(new Scope(ScopeName::Http), function () use ($pipeline): void {
            $pipeline->callAction(
                DummyController::class,
                'index',
                ['name' => 'Antony']
            );
        });
    }
}
