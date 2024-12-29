<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use PHPUnit\Framework\Attributes\Group;
use Spiral\Core\Container;
use Spiral\Core\Exception\Scope\FinalizersException;
use Spiral\Tests\Core\Scope\Stub\AttrFinalize;
use Spiral\Tests\Core\Scope\Stub\AttrScopeFooFinalize;
use Spiral\Tests\Core\Scope\Stub\FileLogger;
use Spiral\Tests\Core\Scope\Stub\LoggerInterface;

final class FinalizeAttributeTest extends BaseTestCase
{
    /**
     * Finalizer from a attribute should be registered and called when a related scope is destroyed.
     */
    public function testFinalizerHasBeenRegisteredAndRun(): void
    {
        $root = self::makeContainer();

        $obj = $root->runScoped(static function (Container $c1) {
            $obj = $c1->runScoped(static function (Container $c2) {
                $obj = $c2->get(AttrScopeFooFinalize::class);

                self::assertFalse($obj->finalized);
                return $obj;
            });

            self::assertFalse($obj->finalized);
            return $obj;
        }, name: 'foo');

        // Finalizer should be called when the scope `foo` is destroyed.
        self::assertTrue($obj->finalized);
    }

    /**
     * Finalizer should be autowired.
     */
    public function testFinalizerAutowiringOnCall(): void
    {
        $root = self::makeContainer();
        $root->bindSingleton(LoggerInterface::class, FileLogger::class);

        $obj2 = null;
        $obj = $root->runScoped(static function (Container $c1) use (&$obj2) {
            $obj = $c1->runScoped(static function (Container $c2) use (&$obj2) {
                $obj = $c2->get(AttrScopeFooFinalize::class);
                $obj2 = $c2->get(AttrScopeFooFinalize::class);

                self::assertNotSame($obj, $obj2);
                self::assertNull($obj->logger);
                self::assertNull($obj2->logger);
                return $obj;
            });

            self::assertNull($obj2->logger);
            self::assertNull($obj->logger);
            return $obj;
        }, name: 'foo');


        self::assertInstanceOf(FileLogger::class, $obj2->logger);
        self::assertInstanceOf(FileLogger::class, $obj->logger);
        self::assertSame($obj2->logger, $obj->logger);
    }

    /**
     * Finalizer without any scope constraint should be called when its scope is destroyed.
     */
    public function testFinalizerWithoutConcreteScope(): void
    {
        $root = self::makeContainer();
        $root->bindSingleton(LoggerInterface::class, FileLogger::class);

        $obj2 = null;
        $obj = $root->runScoped(static function (Container $c1) use (&$obj2) {
            $obj = $c1->runScoped(static function (Container $c2) use (&$obj2) {
                $obj = $c2->get(AttrFinalize::class);
                $obj2 = $c2->get(AttrFinalize::class);

                self::assertNotSame($obj, $obj2);
                self::assertNull($obj->logger);
                self::assertNull($obj2->logger);
                return $obj;
            });

            self::assertNull($obj2->logger);
            self::assertNull($obj->logger);
            return $obj;
        }, bindings: [AttrFinalize::class => AttrFinalize::class], name: 'foo');


        self::assertInstanceOf(FileLogger::class, $obj2->logger);
        self::assertInstanceOf(FileLogger::class, $obj->logger);
        self::assertSame($obj2->logger, $obj->logger);
    }

    /**
     * Finalizer without any scope constraint should be called when its scope is destroyed even if the scope is root.
     */
    public function testFinalizerWithoutConcreteScopeInRoot(): void
    {
        $root = self::makeContainer();
        $root->bindSingleton(LoggerInterface::class, FileLogger::class);

        $obj = $root->get(AttrFinalize::class);

        self::assertNull($obj->logger);

        // Destroy the root scope.
        unset($root);
        self::assertInstanceOf(LoggerInterface::class, $obj->logger);
    }

    #[Group('scrutinizer-ignore')]
    public function testExceptionOnDestroy()
    {
        $root = self::makeContainer();

        self::expectException(FinalizersException::class);
        self::expectExceptionMessage('An exception has been thrown during finalization of the scope `foo`');

        try {
            $root->runScoped(static function (Container $c1): void {
                $obj = $c1->get(AttrScopeFooFinalize::class);
                $obj->throwException = true;
            }, name: 'foo');
        } catch (FinalizersException $e) {
            self::assertSame('foo', $e->getScope());
            self::assertCount(1, $e->getExceptions());
            // Contains the message from the inner exception.
            self::assertStringContainsString(
                'Test exception from finalize method',
                $e->getMessage(),
            );
            self::assertStringContainsString(
                'Test exception from finalize method',
                $e->getExceptions()[0]->getMessage(),
            );
            throw $e;
        }
    }

    #[Group('scrutinizer-ignore')]
    public function testManyExceptionsOnDestroy()
    {
        $root = self::makeContainer();

        self::expectException(FinalizersException::class);
        self::expectExceptionMessage('3 exceptions have been thrown during finalization of the scope `foo`');

        try {
            $root->runScoped(static function (Container $c1): void {
                $c1->get(AttrScopeFooFinalize::class)->throwException = true;
                $c1->get(AttrScopeFooFinalize::class)->throwException = true;
                $c1->get(AttrScopeFooFinalize::class)->throwException = true;
            }, name: 'foo');
        } catch (FinalizersException $e) {
            self::assertSame('foo', $e->getScope());
            self::assertCount(3, $e->getExceptions());
            // Contains the message from the inner exception.
            self::assertStringContainsString(
                'Test exception from finalize method',
                $e->getMessage(),
            );
            throw $e;
        }
    }
}
