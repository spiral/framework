<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Interceptor;

use Spiral\App\Controller\Demo2Controller;
use Spiral\App\Controller\Demo3Controller;
use Spiral\App\Controller\DemoController;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;
use Spiral\Core\Exception\InterceptorException;
use Spiral\Security\Actor\Actor;
use Spiral\Security\ActorInterface;
use Spiral\Tests\Framework\BaseTest;

final class GuardedTest extends BaseTest
{
    private function getCore(): CoreInterface
    {
        return $this->getContainer()->get(CoreInterface::class);
    }

    public function testInvalidAnnotationConfiguration(): void
    {
        $core = $this->getCore();

        $this->expectException(InterceptorException::class);
        $core->callAction(DemoController::class, 'guardedButNoName', []);
    }

    public function testInvalidAnnotationConfigurationWithAttribute(): void
    {
        $core = $this->getCore();

        $this->expectException(InterceptorException::class);
        $core->callAction(DemoController::class, 'guardedButNoNameAttribute', []);
    }

    public function testInvalidAnnotationConfigurationIfEmptyGuarded(): void
    {
        $core = $this->getCore();

        $this->expectException(InterceptorException::class);
        $core->callAction(Demo3Controller::class, 'do', []);
    }

    public function testNotAllowed(): void
    {
        $core = $this->getCore();

        $this->expectException(ControllerException::class);
        $core->callAction(DemoController::class, 'do', []);
    }

    public function testNotAllowed2(): void
    {
        $core = $this->getCore();

        $this->expectException(ControllerException::class);
        $core->callAction(Demo2Controller::class, 'do1', []);
    }

    public function testNotAllowedError1(): void
    {
        $core = $this->getCore();

        $this->expectExceptionCode(ControllerException::FORBIDDEN);
        $core->callAction(Demo2Controller::class, 'do1', []);
    }

    public function testNotAllowedError2(): void
    {
        $core = $this->getCore();

        $this->expectExceptionCode(ControllerException::NOT_FOUND);
        $core->callAction(Demo2Controller::class, 'do2', []);
    }


    public function testNotAllowedError3(): void
    {
        $core = $this->getCore();

        $this->expectExceptionCode(ControllerException::ERROR);
        $core->callAction(Demo2Controller::class, 'do3', []);
    }


    public function testNotAllowedError4(): void
    {
        $core = $this->getCore();

        $this->expectExceptionCode(ControllerException::BAD_ACTION);
        $core->callAction(Demo2Controller::class, 'do4', []);
    }

    public function testAllowed(): void
    {
        $core = $this->getCore();

        $this->getContainer()->bind(ActorInterface::class, new Actor(['user']));

        $this->assertSame('ok', $core->callAction(DemoController::class, 'do', []));
    }

    public function testAllowedWithAttribute(): void
    {
        $core = $this->getCore();

        $this->getContainer()->bind(ActorInterface::class, new Actor(['user']));

        $this->assertSame('ok', $core->callAction(DemoController::class, 'doAttribute', []));
    }

    public function testNotAllowed3(): void
    {
        $core = $this->getCore();

        $this->getContainer()->bind(ActorInterface::class, new Actor(['user']));

        $this->expectExceptionCode(ControllerException::FORBIDDEN);
        $this->assertSame('ok', $core->callAction(Demo2Controller::class, 'do1', []));
    }

    public function testAllowed2(): void
    {
        $core = $this->getCore();

        $this->getContainer()->bind(ActorInterface::class, new Actor(['demo']));
        $this->assertSame('ok', $core->callAction(Demo2Controller::class, 'do1', []));
    }

    public function testNotAllowed2WithAttribute(): void
    {
        $core = $this->getCore();

        $this->getContainer()->bind(ActorInterface::class, new Actor(['demo']));
        $this->assertSame('ok', $core->callAction(Demo2Controller::class, 'do1Attribute', []));
    }
}
