<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Debug\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container\Autowire;
use Spiral\Debug\Config\DebugConfig;
use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;

final class DebugConfigTest extends TestCase
{
    #[DataProvider('collectorsDataProvider')]
    public function testGetCollectors(array $collectors): void
    {
        $config = new DebugConfig(['collectors' => $collectors]);
        $this->assertSame($collectors, $config->getCollectors());
    }

    #[DataProvider('tagsDataProvider')]
    public function testGetTags(array $tags): void
    {
        $config = new DebugConfig(['tags' => $tags]);
        $this->assertSame($tags, $config->getTags());
    }

    public static function collectorsDataProvider(): \Traversable
    {
        yield [[]];
        yield [['some']];
        yield [[new Autowire('some')]];
        yield [[ new class implements StateCollectorInterface {
            public function populate(StateInterface $state): void
            {
            }
        }]];
    }

    public static function tagsDataProvider(): \Traversable
    {
        yield [[]];
        yield [['some' => 'value']];
        yield [['some' => static fn (): string => 'value']];
        yield [['some' => static fn (mixed $a): string => 'value']];
        yield [['some' => new class implements \Stringable {public function __toString(): string { return 'value';}}]];
    }
}
