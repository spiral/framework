<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager\Checker;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Attribute\BootloadConfig;
use Spiral\Boot\BootloadManager\Checker\ConfigChecker;
use Spiral\Boot\Environment;
use Spiral\Tests\Boot\Fixtures\BootloaderA;

final class ConfigCheckerTest extends TestCase
{
    public static function canInitializeDataProvider(): \Traversable
    {
        yield [true, null];
        yield [true, new BootloadConfig()];
        yield [false, new BootloadConfig(enabled: false)];

        yield [true, new BootloadConfig(allowEnv: ['APP_ENV' => 'dev'])];
        yield [true, new BootloadConfig(allowEnv: ['APP_ENV' => ['dev']])];
        yield [false, new BootloadConfig(allowEnv: ['APP_ENV' => 'dev', 'DEBUG' => true])];
        yield [false, new BootloadConfig(allowEnv: ['APP_ENV' => 'prod'])];
        yield [false, new BootloadConfig(allowEnv: ['APP_ENV' => ['prod']])];
        yield [false, new BootloadConfig(allowEnv: ['APP_ENV' => ['prod'], 'DEBUG' => true])];

        yield [false, new BootloadConfig(denyEnv: ['APP_ENV' => 'dev'])];
        yield [false, new BootloadConfig(denyEnv: ['APP_ENV' => ['dev']])];
        yield [false, new BootloadConfig(denyEnv: ['APP_ENV' => 'dev', 'DEBUG' => true])];
        yield [true, new BootloadConfig(denyEnv: ['APP_ENV' => 'prod'])];
        yield [true, new BootloadConfig(denyEnv: ['APP_ENV' => ['prod']])];
        yield [true, new BootloadConfig(denyEnv: ['APP_ENV' => ['prod'], 'DEBUG' => true])];
    }

    #[DataProvider('canInitializeDataProvider')]
    public function testCanInitialize(bool $expected, ?BootloadConfig $config = null): void
    {
        $checker = new ConfigChecker(new Environment([
            'APP_ENV' => 'dev',
        ]));

        self::assertSame($expected, $checker->canInitialize(BootloaderA::class, $config));
    }
}
