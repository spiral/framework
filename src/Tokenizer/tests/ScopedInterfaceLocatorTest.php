<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use Spiral\Core\Container;
use Spiral\Tests\Tokenizer\Interfaces\BadInterface;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceA;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceB;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceC;
use Spiral\Tests\Tokenizer\Interfaces\Excluded\InterfaceXX;
use Spiral\Tests\Tokenizer\Interfaces\Inner\InterfaceD;
use Spiral\Tokenizer\ScopedInterfaceLocator;
use Spiral\Tokenizer\ScopedInterfacesInterface;
use Spiral\Tokenizer\Tokenizer;

final class ScopedInterfaceLocatorTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->bind(Tokenizer::class, $this->getTokenizer(['scopes' => [
            'foo' => ['directories' => [__DIR__ . '/Interfaces/Inner'], 'exclude' => []],
        ]]));
        $this->container->bindSingleton(ScopedInterfacesInterface::class, ScopedInterfaceLocator::class);
    }

    public function testGetsInterfacesForExistsScope(): void
    {
        $classes = $this->container->get(ScopedInterfacesInterface::class)->getScopedInterfaces('foo');

        self::assertArrayHasKey(InterfaceD::class, $classes);

        // Excluded
        self::assertArrayNotHasKey(InterfaceA::class, $classes);
        self::assertArrayNotHasKey(InterfaceB::class, $classes);
        self::assertArrayNotHasKey(InterfaceC::class, $classes);
        self::assertArrayNotHasKey(InterfaceXX::class, $classes);
        self::assertArrayNotHasKey(BadInterface::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Interfaces\Bad_Interface', $classes);
    }

    public function testGetsInterfacesForNotExistScope(): void
    {
        $classes = $this->container->get(ScopedInterfacesInterface::class)->getScopedInterfaces('bar');

        self::assertArrayHasKey(InterfaceA::class, $classes);
        self::assertArrayHasKey(InterfaceB::class, $classes);
        self::assertArrayHasKey(InterfaceC::class, $classes);
        self::assertArrayHasKey(InterfaceD::class, $classes);

        // Excluded
        self::assertArrayNotHasKey(InterfaceXX::class, $classes);
        self::assertArrayNotHasKey(BadInterface::class, $classes);
        self::assertArrayNotHasKey('Spiral\Tests\Tokenizer\Interfaces\Bad_Interface', $classes);
    }
}
