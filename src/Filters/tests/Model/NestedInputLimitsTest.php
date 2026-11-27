<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Model;

use Nyholm\Psr7\ServerRequest;
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
use Spiral\Tests\Filters\BaseTestCase;
use Spiral\Tests\Filters\Fixtures\TreeFilter;

final class NestedInputLimitsTest extends BaseTestCase
{
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

        $provider = $this->container->make(FilterProvider::class, ['core' => new Core()]);
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
