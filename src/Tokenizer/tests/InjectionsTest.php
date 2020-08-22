<?php

/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;

class InjectionsTest extends TestCase
{
    public function testClassLocator()
    {
        $container = new Container();
        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $container->bindSingleton(ClassesInterface::class, ClassLocator::class);

        $this->assertInstanceOf(
            ClassLocator::class,
            $container->get(ClassesInterface::class)
        );
    }

    public function testInvocationsLocator()
    {
        $container = new Container();
        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => []
        ]));

        $container->bindSingleton(InvocationsInterface::class, InvocationLocator::class);

        $this->assertInstanceOf(
            InvocationLocator::class,
            $container->get(InvocationsInterface::class)
        );
    }
}
