<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use Spiral\Core\Exception\ControllerException;
use Spiral\Framework\ScopeName;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Testing\TestCase;
use Spiral\Tests\Core\Fixtures\CleanController;
use Spiral\Tests\Core\Fixtures\DummyController;
use Spiral\Tests\Core\Fixtures\SampleCore;

final class CoreTest extends TestCase
{
    #[TestScope(ScopeName::Http)]
    public function testCallAction(): void
    {
        $core = new SampleCore($this->getContainer());
        $this->assertSame('Hello, Antony.', $core->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCallActionDefaultParameter(): void
    {
        $core = new SampleCore($this->getContainer());
        $this->assertSame('Hello, Dave.', $core->callAction(
            DummyController::class,
            'index'
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCallActionDefaultAction(): void
    {
        $core = new SampleCore($this->getContainer());
        $this->assertSame('Hello, Dave.', $core->callAction(
            DummyController::class,
            'index'
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCallActionDefaultActionWithParameter(): void
    {
        $core = new SampleCore($this->getContainer());
        $this->assertSame('Hello, Antony.', $core->callAction(
            DummyController::class,
            'index',
            ['name' => 'Antony']
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCallActionMissingParameter(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'required');
    }

    #[TestScope(ScopeName::Http)]
    public function testCallActionInvalidParameter(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'required', ['id' => null]);
    }

    #[TestScope(ScopeName::Http)]
    public function testCallWrongController(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(BadController::class, 'index', ['name' => 'Antony']);
    }

    #[TestScope(ScopeName::Http)]
    public function testCallBadAction(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'missing', [
            'name' => 'Antony',
        ]);
    }

    #[TestScope(ScopeName::Http)]
    public function testStaticAction(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'inner');
    }

    #[TestScope(ScopeName::Http)]
    public function testInheritedAction(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'execute');
    }

    #[TestScope(ScopeName::Http)]
    public function testInheritedActionCall(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(DummyController::class, 'call');
    }

    #[TestScope(ScopeName::Http)]
    public function testCallNotController(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $core->callAction(SampleCore::class, 'index', [
            'name' => 'Antony',
        ]);
    }

    #[TestScope(ScopeName::Http)]
    public function testCleanController(): void
    {
        $core = new SampleCore($this->getContainer());
        $this->assertSame('900', $core->callAction(
            CleanController::class,
            'test',
            ['id' => '900']
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCleanControllerError(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $this->assertSame('900', $core->callAction(
            CleanController::class,
            'test',
            ['id' => null]
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCleanControllerError2(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $this->assertSame('900', $core->callAction(
            CleanController::class,
            'test',
            []
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCleanControllerError3(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $this->assertSame('900', $core->callAction(
            CleanController::class,
            'invalid',
            []
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testCleanControllerError4(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $this->assertSame('900', $core->callAction(
            CleanController::class,
            'another',
            []
        ));
    }

    #[TestScope(ScopeName::Http)]
    public function testMissingDependency(): void
    {
        $this->expectException(ControllerException::class);

        $core = new SampleCore($this->getContainer());
        $this->assertSame('900', $core->callAction(
            CleanController::class,
            'missing',
            []
        ));
    }
}
