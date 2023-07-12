<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tests\Tokenizer\Interfaces\BadInterface;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceA;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceB;
use Spiral\Tests\Tokenizer\Interfaces\InterfaceC;
use Spiral\Tests\Tokenizer\Interfaces\Excluded\InterfaceXX;
use Spiral\Tests\Tokenizer\Interfaces\Inner\InterfaceD;
use Spiral\Tokenizer\Config\TokenizerConfig;
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
        $this->container->bind(Tokenizer::class, $this->getTokenizer());
        $this->container->bindSingleton(ScopedInterfacesInterface::class, ScopedInterfaceLocator::class);
    }

    public function testGetsInterfacesForExistsScope()
    {
        $classes = $this->container->get(ScopedInterfacesInterface::class)->getScopedInterfaces('foo');

        $this->assertArrayHasKey(InterfaceD::class, $classes);

        // Excluded
        $this->assertArrayNotHasKey(InterfaceA::class, $classes);
        $this->assertArrayNotHasKey(InterfaceB::class, $classes);
        $this->assertArrayNotHasKey(InterfaceC::class, $classes);
        $this->assertArrayNotHasKey(InterfaceXX::class, $classes);
        $this->assertArrayNotHasKey(BadInterface::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Interfaces\Bad_Interface', $classes);
    }

    public function testGetsInterfacesForNotExistScope()
    {
        $classes = $this->container->get(ScopedInterfacesInterface::class)->getScopedInterfaces('bar');

        $this->assertArrayHasKey(InterfaceA::class, $classes);
        $this->assertArrayHasKey(InterfaceB::class, $classes);
        $this->assertArrayHasKey(InterfaceC::class, $classes);
        $this->assertArrayHasKey(InterfaceD::class, $classes);

        // Excluded
        $this->assertArrayNotHasKey(InterfaceXX::class, $classes);
        $this->assertArrayNotHasKey(BadInterface::class, $classes);
        $this->assertArrayNotHasKey('Spiral\Tests\Tokenizer\Interfaces\Bad_Interface', $classes);
    }

    protected function getTokenizer(): Tokenizer
    {
        $config = new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude' => ['Excluded'],
            'scopes' => [
                'foo' => [
                    'directories' => [__DIR__.'/Interfaces/Inner'],
                    'exclude' => [],
                ],
            ],
        ]);

        return new Tokenizer($config);
    }
}
