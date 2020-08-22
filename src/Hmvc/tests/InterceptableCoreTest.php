<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\InterceptorException;
use Spiral\Core\InterceptableCore;
use Spiral\Core\InterceptorPipeline;
use Spiral\Tests\Core\Fixtures\DummyController;
use Spiral\Tests\Core\Fixtures\SampleCore;

class InterceptableCoreTest extends TestCase
{
    public function testNoInterceptors(): void
    {
        $core = new SampleCore(new Container());
        $int = new InterceptableCore($core);

        $this->assertSame('Hello, Antony.', $int->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        ));
    }

    public function testNoInterceptors2(): void
    {
        $core = new SampleCore(new Container());
        $int = new InterceptableCore($core);
        $int->addInterceptor(new DemoInterceptor());

        $this->assertSame('?Hello, Antony.!', $int->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        ));
    }

    public function testNoInterceptors22(): void
    {
        $core = new SampleCore(new Container());
        $int = new InterceptableCore($core);
        $int->addInterceptor(new DemoInterceptor());
        $int->addInterceptor(new DemoInterceptor());

        $this->assertSame('??Hello, Antony.!!', $int->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        ));
    }

    public function testInvalidPipeline(): void
    {
        $this->expectException(InterceptorException::class);

        $pipeline = new InterceptorPipeline();
        $pipeline->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        );
    }
}
