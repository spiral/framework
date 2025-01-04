<?php

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\EnumLocator;
use Spiral\Tokenizer\EnumsInterface;
use Spiral\Tokenizer\InterfaceLocator;
use Spiral\Tokenizer\InterfacesInterface;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;

class InjectionsTest extends TestCase
{
    public function testClassLocator(): void
    {
        $container = new Container();
        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $container->bindSingleton(ClassesInterface::class, ClassLocator::class);

        self::assertInstanceOf(ClassLocator::class, $container->get(ClassesInterface::class));
    }

    public function testInvocationsLocator(): void
    {
        $container = new Container();
        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $container->bindSingleton(InvocationsInterface::class, InvocationLocator::class);

        self::assertInstanceOf(InvocationLocator::class, $container->get(InvocationsInterface::class));
    }

    public function testEnumsLocator(): void
    {
        $container = new Container();
        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $container->bindSingleton(EnumsInterface::class, EnumLocator::class);

        self::assertInstanceOf(EnumLocator::class, $container->get(EnumsInterface::class));
    }

    public function testInterfacesLocator(): void
    {
        $container = new Container();
        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $container->bindSingleton(InterfacesInterface::class, InterfaceLocator::class);

        self::assertInstanceOf(InterfaceLocator::class, $container->get(InterfacesInterface::class));
    }
}
