<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use PHPUnit\Framework\Attributes\Group;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Tests\Core\Scope\Stub\DatetimeCarrier;
use Spiral\Tests\Core\Scope\Stub\ExceptionConstructor;

#[Group('scrutinizer-ignore')]
final class ExceptionsTest extends BaseTestCase
{
    public function testParentScopeResolvingCustomException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(ExceptionConstructor::MESSAGE);

        $container = new Container();

        $container->runScoped(static function (Container $c1): void {
            try {
                $c1->get(ExceptionConstructor::class);
                self::fail('Exception should be thrown');
            } catch (\Throwable $e) {
                self::assertInstanceOf(\Exception::class, $e);
                throw $e;
            }
        });
    }

    public function testParentScopeThrowConstructorErrorOnResolving(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(ExceptionConstructor::MESSAGE);

        $container = new Container();

        $container->runScoped(static function (Container $c1): void {
            try {
                $c1->get(ExceptionConstructor::class);
                self::fail('Exception should be thrown');
            } catch (\Throwable $e) {
                self::assertInstanceOf(\Exception::class, $e);
                throw $e;
            }
        });
    }

    public function testParentScopeResolvingNotFound(): void
    {
        self::expectException(NotFoundException::class);
        self::expectExceptionMessage("jump to scope: 'root'");

        $container = new Container();

        $container->runScoped(static function (Container $c1): void {
            try {
                $c1->get(DatetimeCarrier::class);
                self::fail('Exception should be thrown');
            } catch (\Throwable $e) {
                self::assertInstanceOf(NotFoundException::class, $e);
                self::assertInstanceOf(NotFoundException::class, $e->getPrevious());
                self::assertStringContainsString(
                    "Can't autowire `DateTimeInterface`: class or injector not found",
                    $e->getPrevious()->getMessage(),
                );

                throw $e;
            }
        });
    }
}
