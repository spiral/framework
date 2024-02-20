<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Config;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Options;

final class RegistryTest extends TestCase
{
    #[DataProvider('configDataProvider')]
    public function testGetConfig(Options $options): void
    {
        $registry = new Registry(
            config: $this->createMock(Config::class),
            options: $options
        );

        $this->assertSame($options, $registry->getOptions());
    }

    public static function configDataProvider(): \Traversable
    {
        yield [new Options()];

        $option = new Options();
        $option->checkScope = true;
        yield [$option];

        $option = new class extends Options {
            public int $customOption = 3;
        };
        $option->customOption = 5;
        $option->checkScope = true;
        yield [$option];
    }
}
