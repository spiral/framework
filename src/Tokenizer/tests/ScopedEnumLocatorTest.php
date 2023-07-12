<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tests\Tokenizer\Enums\BadEnum;
use Spiral\Tests\Tokenizer\Enums\EnumA;
use Spiral\Tests\Tokenizer\Enums\EnumB;
use Spiral\Tests\Tokenizer\Enums\EnumC;
use Spiral\Tests\Tokenizer\Enums\Excluded\EnumXX;
use Spiral\Tests\Tokenizer\Enums\Inner\EnumD;
use Spiral\Tokenizer\Config\TokenizerConfig;
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
        $this->container->bind(Tokenizer::class, $this->getTokenizer());
        $this->container->bindSingleton(ScopedEnumsInterface::class, ScopedEnumLocator::class);
    }

    public function testGetsEnumsForExistsScope()
    {
        $classes = $this->container->get(ScopedEnumsInterface::class)->getScopedEnums('foo');

        $this->assertArrayHasKey(EnumD::class, $classes);

        // Excluded
        $this->assertArrayNotHasKey(EnumA::class, $classes);
        $this->assertArrayNotHasKey(EnumB::class, $classes);
        $this->assertArrayNotHasKey(EnumC::class, $classes);
        $this->assertArrayNotHasKey(EnumXX::class, $classes);
        $this->assertArrayNotHasKey(BadEnum::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Enums\Bad_Enum', $classes);
    }

    public function testGetsEnumsForNotExistScope()
    {
        $classes = $this->container->get(ScopedEnumsInterface::class)->getScopedEnums('bar');

        $this->assertArrayHasKey(EnumA::class, $classes);
        $this->assertArrayHasKey(EnumB::class, $classes);
        $this->assertArrayHasKey(EnumC::class, $classes);
        $this->assertArrayHasKey(EnumD::class, $classes);

        // Excluded
        $this->assertArrayNotHasKey(EnumXX::class, $classes);
        $this->assertArrayNotHasKey(BadEnum::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Enums\Bad_Enum', $classes);
    }

    protected function getTokenizer(): Tokenizer
    {
        $config = new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude' => ['Excluded'],
            'scopes' => [
                'foo' => [
                    'directories' => [__DIR__.'/Enums/Inner'],
                    'exclude' => [],
                ],
            ],
        ]);

        return new Tokenizer($config);
    }
}
