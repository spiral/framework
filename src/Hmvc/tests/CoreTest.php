<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use Spiral\Core\Container;
use Spiral\Core\Exception\ControllerException;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\TestCase;
use Spiral\Tests\Core\Fixtures\CleanController;
use Spiral\Tests\Core\Fixtures\DummyController;
use Spiral\Tests\Core\Fixtures\SampleCore;
use Spiral\Tests\Core\Fixtures\TestService;

#[TestScope('http')]
final class CoreTest extends TestCase
{
    public function testCallAction(): void
    {
        $core = new SampleCore($this->getContainer());
        self::assertSame('Hello, Antony.', $core->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        ));
    }

    public function testCallActionDefaultParameter(): void
    {
        $core = new SampleCore($this->getContainer());
        self::assertSame('Hello, Dave.', $core->callAction(
            DummyController::class,
            'index'
        ));
    }

    public function testCallActionDefaultAction(): void
    {
        $core = new SampleCore($this->getContainer());
        self::assertSame('Hello, Dave.', $core->callAction(
            DummyController::class,
            'index'
        ));
    }

    public function testCallActionDefaultActionWithParameter(): void
    {
        $core = new SampleCore($this->getContainer());
        self::assertSame('Hello, Antony.', $core->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        ));
    }

    public function testCallActionMissingParameter(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'required');
    }

    public function testCallActionInvalidParameter(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'required', ['id' => null]);
    }

    public function testCallWrongController(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(BadController::class, 'index', ['name' => 'Antony']);
    }

    public function testCallBadAction(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'missing', [
            'name' => 'Antony',
        ]);
    }

    public function testStaticAction(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'inner');
    }

    public function testInheritedAction(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'execute');
    }

    public function testInheritedActionCall(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'call');
    }

    public function testCallNotController(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(SampleCore::class, 'index', [
            'name' => 'Antony',
        ]);
    }

    public function testCleanController(): void
    {
        $core = new SampleCore($this->getContainer());
        self::assertSame('900', $core->callAction(
            CleanController::class,
            'test',
            ['id' => '900']
        ));
    }

    public function testCleanControllerError(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        self::assertSame('900', $core->callAction(
            CleanController::class,
            'test',
            ['id' => null]
        ));
    }

    public function testCleanControllerError2(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        self::assertSame('900', $core->callAction(
            CleanController::class,
            'test',
            []
        ));
    }

    public function testCleanControllerError3(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        self::assertSame('900', $core->callAction(
            CleanController::class,
            'invalid',
            []
        ));
    }

    public function testCleanControllerError4(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        self::assertSame('900', $core->callAction(
            CleanController::class,
            'another',
            []
        ));
    }

    public function testMissingDependency(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        self::assertSame('900', $core->callAction(
            CleanController::class,
            'missing',
            []
        ));
    }

    public function testCallActionReflectionMethodFromExtendedAbstractClass(): void
    {
        $handler = new SampleCore($this->getContainer());

        $result = $handler->callAction(TestService::class, 'parentMethod', ['HELLO']);

        self::assertSame('hello', $result);
    }

    public function testHandleReflectionMethodFromExtendedAbstractClass(): void
    {
        $handler = new SampleCore($this->getContainer());
        // Call Context
        $ctx = (new CallContext(Target::fromPair(TestService::class, 'parentMethod')))
            ->withArguments(['HELLO']);

        $result = $handler->handle($ctx);

        self::assertSame('hello', $result);
    }

    public function testHandleReflectionMethodWithObject(): void
    {
        $c = new Container();
        $handler = new SampleCore($c);
        // Call Context
        $service = new TestService();
        $ctx = (new CallContext(Target::fromPair($service, 'parentMethod')->withPath(['foo', 'bar'])))
            ->withArguments(['HELLO']);

        $result = $handler->handle($ctx);

        self::assertSame('hello', $result);
    }
}
