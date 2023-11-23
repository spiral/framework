<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\BootloadManager\Checker;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Attribute\BootloaderRules;
use Spiral\Boot\BootloadManager\Checker\ClassExistsChecker;
use Spiral\Boot\BootloadManager\Checker\RulesChecker;
use Spiral\Boot\Environment;
use Spiral\Boot\Exception\ClassNotFoundException;
use Spiral\Tests\Boot\Fixtures\BootloaderA;

final class RulesCheckerTest extends TestCase
{
    #[DataProvider('canInitializeDataProvider')]
    public function testCanInitialize(bool $expected, ?BootloaderRules $rules = null): void
    {
        $checker = new RulesChecker(new Environment([
            'APP_ENV' => 'dev'
        ]));

        $this->assertSame($expected, $checker->canInitialize(BootloaderA::class, $rules));
    }

    public function testCanInitializeException(): void
    {
        $checker = new ClassExistsChecker();

        $this->expectException(ClassNotFoundException::class);
        $checker->canInitialize('foo');
    }

    public static function canInitializeDataProvider(): \Traversable
    {
        yield [true, null];
        yield [true, new BootloaderRules()];
        yield [false, new BootloaderRules(enabled: false)];

        yield [true, new BootloaderRules(allowEnv: ['APP_ENV' => 'dev'])];
        yield [true, new BootloaderRules(allowEnv: ['APP_ENV' => ['dev']])];
        yield [false, new BootloaderRules(allowEnv: ['APP_ENV' => 'dev', 'DEBUG' => true])];
        yield [false, new BootloaderRules(allowEnv: ['APP_ENV' => 'prod'])];
        yield [false, new BootloaderRules(allowEnv: ['APP_ENV' => ['prod']])];
        yield [false, new BootloaderRules(allowEnv: ['APP_ENV' => ['prod'], 'DEBUG' => true])];

        yield [false, new BootloaderRules(denyEnv: ['APP_ENV' => 'dev'])];
        yield [false, new BootloaderRules(denyEnv: ['APP_ENV' => ['dev']])];
        yield [false, new BootloaderRules(denyEnv: ['APP_ENV' => 'dev', 'DEBUG' => true])];
        yield [true, new BootloaderRules(denyEnv: ['APP_ENV' => 'prod'])];
        yield [true, new BootloaderRules(denyEnv: ['APP_ENV' => ['prod']])];
        yield [true, new BootloaderRules(denyEnv: ['APP_ENV' => ['prod'], 'DEBUG' => true])];
    }
}
