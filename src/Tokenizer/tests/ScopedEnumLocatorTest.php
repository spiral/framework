<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use Spiral\Core\Container;
use Spiral\Tests\Tokenizer\Enums\BadEnum;
use Spiral\Tests\Tokenizer\Enums\EnumA;
use Spiral\Tests\Tokenizer\Enums\EnumB;
use Spiral\Tests\Tokenizer\Enums\EnumC;
use Spiral\Tests\Tokenizer\Enums\Excluded\EnumXX;
use Spiral\Tests\Tokenizer\Enums\Inner\EnumD;
use Spiral\Tokenizer\ScopedEnumLocator;
use Spiral\Tokenizer\ScopedEnumsInterface;
use Spiral\Tokenizer\Tokenizer;

final class ScopedEnumLocatorTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->bind(Tokenizer::class, $this->getTokenizer([
            'scopes' => ['foo' => ['directories' => [__DIR__ . '/Enums/Inner'], 'exclude' => []]],
        ]));
        $this->container->bindSingleton(ScopedEnumsInterface::class, ScopedEnumLocator::class);
    }

    public function testGetsEnumsForExistsScope(): void
    {
        $classes = $this->container->get(ScopedEnumsInterface::class)->getScopedEnums('foo');

        self::assertArrayHasKey(EnumD::class, $classes);

        // Excluded
        self::assertArrayNotHasKey(EnumA::class, $classes);
        self::assertArrayNotHasKey(EnumB::class, $classes);
        self::assertArrayNotHasKey(EnumC::class, $classes);
        self::assertArrayNotHasKey(EnumXX::class, $classes);
        self::assertArrayNotHasKey(BadEnum::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Enums\Bad_Enum', $classes);
    }

    public function testGetsEnumsForNotExistScope(): void
    {
        $classes = $this->container->get(ScopedEnumsInterface::class)->getScopedEnums('bar');

        self::assertArrayHasKey(EnumA::class, $classes);
        self::assertArrayHasKey(EnumB::class, $classes);
        self::assertArrayHasKey(EnumC::class, $classes);
        self::assertArrayHasKey(EnumD::class, $classes);

        // Excluded
        self::assertArrayNotHasKey(EnumXX::class, $classes);
        self::assertArrayNotHasKey(BadEnum::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Enums\Bad_Enum', $classes);
    }
}
