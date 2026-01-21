<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Loader\DirectoriesRepositoryInterface;
use Spiral\Tests\Boot\Fixtures\FooConfig;
use Spiral\Tests\Boot\Fixtures\TestConfig;
use Spiral\Tests\Boot\Fixtures\TestCore;
use Traversable;

class ConfigsTest extends TestCase
{
    public function testDirectories(): void
    {
        $core = TestCore::create([
            'root' => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run();

        /** @var TestConfig $testConfig */
        $testConfig = $core->getContainer()->get(TestConfig::class);

        /** @var FooConfig $fooConfig */
        $fooConfig = $core->getContainer()->get(FooConfig::class);

        self::assertSame(['key' => 'value1'], $testConfig->toArray());
        self::assertSame(['key' => 'value'], $fooConfig->toArray());
    }

    public function testCustomDirectoriesRepository(): void
    {
        $core = TestCore::create([
            'root' => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run();

        $core->getContainer()->bindSingleton(
            DirectoriesRepositoryInterface::class,
            static function (DirectoriesInterface $dirs): DirectoriesRepositoryInterface {
                return new class($dirs->get('config')) implements DirectoriesRepositoryInterface {
                    public function __construct(
                        private string $rootDir,
                    ) {}


                    public function getIterator(): Traversable
                    {
                        yield $this->rootDir;
                    }
                };
            },
        );

        /** @var TestConfig $testConfig */
        $testConfig = $core->getContainer()->get(TestConfig::class);

        /** @var FooConfig $fooConfig */
        $fooConfig = $core->getContainer()->get(FooConfig::class);

        self::assertSame(['key' => 'value'], $testConfig->toArray());
        self::assertSame(['key' => 'value'], $fooConfig->toArray());
    }

    public function testCustomConfigLoader(): void
    {
        $core = TestCore::create([
            'root' => __DIR__,
            'config' => __DIR__ . '/config',
        ])->run();

        /** @var ConfiguratorInterface $configurator */
        $configurator = $core->getContainer()->get(ConfiguratorInterface::class);

        self::assertSame(['test-key' => 'test value'], $configurator->getConfig('yaml'));
    }
}
