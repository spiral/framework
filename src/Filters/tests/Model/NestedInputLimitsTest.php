<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\TestWith;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Filter\InputScope;
use Spiral\Filters\Config\FiltersConfig;
use Spiral\Filters\Exception\NestedInputLimitException;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\Model\FilterProvider;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Interceptor\Core;
use Spiral\Filters\Model\Mapper\CasterRegistry;
use Spiral\Filters\Model\Mapper\CasterRegistryInterface;
use Spiral\Filters\Model\NestedInputLimits;
use Spiral\Http\Request\InputManager;
use Spiral\Interceptors\Context\CallContextInterface;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Tests\Filters\BaseTestCase;
use Spiral\Tests\Filters\Fixtures\TreeFilter;

final class NestedInputLimitsTest extends BaseTestCase
{
    /** How many filters the provider has finished building, the root one included. */
    private int $builds = 0;

    public function testBuildsFilterWithinLimits(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: 100, maxNestedKeyLength: 8);

        $filter = $provider->createFilter(TreeFilter::class, $this->input([
            'branches' => [
                'a' => ['leaves' => [['name' => 'x'], ['name' => 'y']]],
                'b' => ['leaves' => [['name' => 'z']]],
            ],
        ]));

        self::assertInstanceOf(TreeFilter::class, $filter);
        self::assertSame('y', $filter->branches['a']->leaves[1]->name);
        self::assertSame('z', $filter->branches['b']->leaves[0]->name);
    }

    public function testRejectsTooManyNestedFilters(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: 10, maxNestedKeyLength: false);

        try {
            $provider->createFilter(TreeFilter::class, $this->input([
                'branches' => [['leaves' => \array_fill(0, 100, ['name' => 'x'])]],
            ]));
            self::fail('The limit is not applied.');
        } catch (NestedInputLimitException $e) {
            self::assertInstanceOf(ValidationException::class, $e);
            self::assertSame('The number of nested filters exceeds the limit of 10.', $e->getMessage());
            self::assertArrayHasKey(0, $e->errors['branches']);
            self::assertArrayHasKey('leaves', $e->errors['branches'][0]);
        }
    }

    public function testCountsNestedFiltersPerRootFilter(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: 10, maxNestedKeyLength: false);
        $data = ['branches' => [['leaves' => [['name' => 'x']]]]];

        for ($i = 0; $i < 5; ++$i) {
            self::assertInstanceOf(TreeFilter::class, $provider->createFilter(TreeFilter::class, $this->input($data)));
        }
    }

    public function testRejectsTooLongNestedArrayKey(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: false, maxNestedKeyLength: 8);

        try {
            $provider->createFilter(TreeFilter::class, $this->input([
                'branches' => ['short' => ['leaves' => [\str_repeat('k', 9) => ['name' => 'x']]]],
            ]));
            self::fail('The limit is not applied.');
        } catch (NestedInputLimitException $e) {
            self::assertSame('The key of a nested filter exceeds the limit of 8 bytes.', $e->getMessage());
            self::assertSame(
                ['branches' => ['short' => ['leaves' => ['The key of a nested filter exceeds the limit of 8 bytes.']]]],
                $e->errors,
            );
        }
    }

    public function testStopsOnLongKeyBeforeBuildingItsChildren(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: false, maxNestedKeyLength: 1024);

        $this->expectException(NestedInputLimitException::class);

        // A long key above many elements: each element would carry the key in its input prefix.
        $provider->createFilter(TreeFilter::class, $this->input([
            'branches' => [\str_repeat('è', 100_000) => ['leaves' => \array_fill(0, 100_000, 0)]],
        ]));
    }

    public function testFalseDisablesLimits(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: false, maxNestedKeyLength: false);

        $filter = $provider->createFilter(TreeFilter::class, $this->input([
            'branches' => [\str_repeat('k', 2000) => ['leaves' => \array_fill(0, 50, ['name' => 'x'])]],
        ]));

        self::assertCount(50, $filter->branches[\str_repeat('k', 2000)]->leaves);
    }

    public function testNullKeepsDefaultLimits(): void
    {
        $config = new FiltersConfig(['maxNestedFilters' => null, 'maxNestedKeyLength' => null]);

        self::assertSame((new FiltersConfig())->getMaxNestedFilters(), $config->getMaxNestedFilters());
        self::assertSame((new FiltersConfig())->getMaxNestedKeyLength(), $config->getMaxNestedKeyLength());
    }

    public function testDefaultNestedFiltersLimitWithoutConfig(): void
    {
        $limits = new NestedInputLimits();
        $limits->enterFilter();

        for ($i = 0; $i < 2000; ++$i) {
            $limits->enterFilter();
            $limits->leaveFilter();
        }

        $this->expectException(NestedInputLimitException::class);
        $limits->enterFilter();
    }

    public function testDefaultKeyLengthLimitWithoutConfig(): void
    {
        $limits = new NestedInputLimits();
        $limits->assertKey('items', \str_repeat('k', 1024));

        $this->expectException(NestedInputLimitException::class);
        $limits->assertKey('items', \str_repeat('k', 1025));
    }

    public function testBuildsEachNestedElementOnce(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: false, maxNestedKeyLength: false);

        $provider->createFilter(TreeFilter::class, $this->input([
            'branches' => [
                ['leaves' => [['name' => 'a'], ['name' => 'b'], ['name' => 'c']]],
                ['leaves' => [['name' => 'd'], ['name' => 'e'], ['name' => 'f']]],
            ],
        ]));

        // The root, 2 branches and 6 leaves.
        self::assertSame(9, $this->builds);
    }

    #[TestWith([8, true])]
    #[TestWith([7, false])]
    public function testLimitCountsEveryNestedFilterOnce(int $limit, bool $passes): void
    {
        $provider = $this->makeProvider(maxNestedFilters: $limit, maxNestedKeyLength: false);

        if (!$passes) {
            $this->expectException(NestedInputLimitException::class);
        }

        // 2 branches and 6 leaves below the root.
        $filter = $provider->createFilter(TreeFilter::class, $this->input([
            'branches' => \array_fill(0, 2, ['leaves' => \array_fill(0, 3, ['name' => 'x'])]),
        ]));

        self::assertInstanceOf(TreeFilter::class, $filter);
    }

    public function testRejectsOversizedArrayBeforeBuildingItsElements(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: 10, maxNestedKeyLength: false);

        try {
            $provider->createFilter(TreeFilter::class, $this->input([
                'branches' => \array_fill(0, 1000, ['leaves' => []]),
            ]));
            self::fail('The limit is not applied.');
        } catch (NestedInputLimitException $e) {
            self::assertSame(['branches' => ['The number of nested filters exceeds the limit of 10.']], $e->errors);
        }

        // Building element by element would have completed 10 branches before the limit stopped it.
        self::assertSame(0, $this->builds);
    }

    public function testKeepsTheViolationAsPrevious(): void
    {
        $provider = $this->makeProvider(maxNestedFilters: false, maxNestedKeyLength: 8);

        try {
            $provider->createFilter(TreeFilter::class, $this->input([
                'branches' => ['short' => ['leaves' => [\str_repeat('k', 9) => ['name' => 'x']]]],
            ]));
            self::fail('The limit is not applied.');
        } catch (NestedInputLimitException $e) {
            self::assertSame(422, $e->getCode());

            $innermost = $e;
            while ($innermost->getPrevious() instanceof NestedInputLimitException) {
                $innermost = $innermost->getPrevious();
            }

            self::assertNotSame($e, $innermost);
            self::assertSame($e->getMessage(), $innermost->getMessage());
            self::assertSame([$innermost->reason], $innermost->errors);
            self::assertSame(['branches' => ['short' => ['leaves' => [$e->reason]]]], $e->errors);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container->bindSingleton(ReaderInterface::class, (new Factory())->create());
        $this->container->bindSingleton(CasterRegistryInterface::class, new CasterRegistry([]));
    }

    private function makeProvider(int|false $maxNestedFilters, int|false $maxNestedKeyLength): FilterProvider
    {
        $this->container->bindSingleton(NestedInputLimits::class, new NestedInputLimits(new FiltersConfig([
            'maxNestedFilters' => $maxNestedFilters,
            'maxNestedKeyLength' => $maxNestedKeyLength,
        ])));

        $core = new class($this->builds) implements HandlerInterface {
            private readonly Core $core;

            public function __construct(
                private int &$builds,
            ) {
                $this->core = new Core();
            }

            public function handle(CallContextInterface $context): mixed
            {
                ++$this->builds;

                return $this->core->handle($context);
            }
        };

        $provider = $this->container->make(FilterProvider::class, ['core' => $core]);
        $this->container->bind(FilterProviderInterface::class, $provider);

        return $provider;
    }

    private function input(array $body): InputScope
    {
        $this->container->bind(
            ServerRequestInterface::class,
            (new ServerRequest('POST', '/'))->withParsedBody($body),
        );

        return new InputScope(new InputManager($this->container));
    }
}
