<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use DateTime;
use DateTimeImmutable;
use Fiber;
use Generator;
use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use stdClass;

final class FibersTest extends BaseTestCase
{
    public const TEST_DATA = [
        'foo' => 1,
        'bar' => 42,
        'baz' => 'jump',
    ];

    /**
     * Test single run of {@see functionScopedTestDataIterator()}.
     */
    public function testSingleFiberNestedContainers(): void
    {
        self::assertNull(ContainerScope::getContainer());

        FiberHelper::runInFiber(
            self::functionScopedTestDataIterator(),
            static function (mixed $suspendValue): void {
                self::assertNull(ContainerScope::getContainer());
                self::assertTrue(\in_array($suspendValue, self::TEST_DATA, true));
            },
        );
    }

    /**
     * Test multiple nested run of {@see functionScopedTestDataIterator()}.
     */
    public function testALotOfFibersWithNestedContainers(): void
    {
        self::assertNull(ContainerScope::getContainer());

        $result = FiberHelper::runFiberSequence(
            self::functionScopedTestDataIterator(
                self::functionScopedTestDataIterator(
                    self::functionScopedTestDataIterator(
                        self::functionScopedTestDataIterator(self::functionScopedTestDataIterator()),
                    ),
                ),
            ),
            self::functionScopedTestDataIterator(),
            self::functionScopedTestDataIterator(self::functionScopedTestDataIterator()),
            self::functionScopedTestDataIterator(
                self::functionScopedTestDataIterator(
                    self::functionScopedTestDataIterator(),
                ),
            ),
            self::functionScopedTestDataIterator(),
        );

        self::assertCount(5, $result);
        foreach ($result as $suspendValue) {
            self::assertSame(self::TEST_DATA, $suspendValue);
        }
    }

    /**
     * Test multiple nested run of {@see functionScopedTestDataIterator()}.
     * Tests the scopes are isolated and nested bindings don't leak to the parent scope.
     */
    public function testALotOfFibersWithNestedContainersWithCommonRootContainer(): void
    {
        $container = new Container();
        $result = FiberHelper::runFiberSequence(
            self::functionScopedTestDataIterator(container: $container),
            self::functionScopedTestDataIterator(self::functionScopedTestDataIterator(), $container),
        );

        self::assertCount(2, $result);
        foreach ($result as $suspendValue) {
            self::assertSame(self::TEST_DATA, $suspendValue);
        }
    }

    public function testExceptionProxy(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('test');

        FiberHelper::runInFiber(
            static fn(): mixed => (new Container())->runScoped(
                function (): string {
                    $result = '';
                    $result .= Fiber::suspend('foo');
                    $result .= Fiber::suspend('bar');
                    return $result . Fiber::suspend('error');
                }
            ),
            static fn(string $suspendValue): string => $suspendValue !== 'error'
                ? $suspendValue
                : throw new \RuntimeException('test'),
        );
    }

    public function testCatchThrownException(): void
    {
        $result = FiberHelper::runInFiber(
            static fn(): mixed => (new Container())->runScoped(
                function (): string {
                    $result = '';
                    $result .= Fiber::suspend('foo');
                    $result .= Fiber::suspend('bar');
                    try {
                        $result .= Fiber::suspend('error');
                    } catch (\Throwable $e) {
                        $result .= $e->getMessage();
                    }
                    return $result . Fiber::suspend('baz');
                }
            ),
            static fn(string $suspendValue): string => $suspendValue !== 'error'
                ? $suspendValue
                : throw new \RuntimeException('test'),
        );

        self::assertSame('foobartestbaz', $result);
    }

    /**
     * Builds a function that creates a container, runs few nested scopes and iterates over test data.
     * The function uses {@see Fiber} and {@see ContainerScope}. It has a lot of self assertions.
     *
     * @param null|callable(): mixed $load A function that will be called in the most nested scope after each suspend.
     *
     * @return callable(): array {@see self::TEST_DATA}
     */
    private static function functionScopedTestDataIterator(
        ?callable $load = null,
        ?Container $container = null,
    ): callable {
        return static function () use ($load, $container): array {
            // The function should be called in a fiber
            self::assertNotNull(Fiber::getCurrent());

            // The function uses its own container
            $c1 = $container ?? new Container();
            $c1->bindSingleton('resource', new stdClass());

            $result = $c1->runScoped(static function (Container $c2) use ($load) {
                // check local binding
                self::assertTrue($c2->has('foo'));
                self::assertInstanceOf(DateTime::class, $c2->get('foo'));

                return $c2->runScoped(
                    static function (ContainerInterface $c3) use ($load) {
                        // check local binding
                        self::assertTrue($c3->has('bar'));

                        $resource = $c3->get('resource');
                        self::assertInstanceOf(DateTimeImmutable::class, $c3->get('bar'));
                        self::assertInstanceOf(stdClass::class, $resource);
                        foreach (self::TEST_DATA as $key => $value) {
                            $resource->$key = $value;
                            $load === null or $load();
                            Fiber::suspend($value);
                            self::assertSame($c3, ContainerScope::getContainer());
                        }
                        return $resource;
                    },
                    ['bar' => new DateTimeImmutable()],
                );
            }, ['foo' => new DateTime()]);
            self::assertFalse($c1->has('foo'));

            self::assertSame(self::TEST_DATA, (array) $result);
            return (array) $result;
        };
    }
}
