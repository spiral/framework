<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Listener;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\Tokenizer\Classes\Listeners\RouteListener;
use Spiral\Tests\Tokenizer\Classes\Targets\ConsoleCommandInterface;
use Spiral\Tokenizer\Listener\ListenerDefinition;

final class ListenerDefinitionTest extends TestCase
{
    public function testGetHash(): void
    {
        $this->assertSame(
            '8d25a7ac9a9f4d4ea4342b8c3b4dc790',
            (new ListenerDefinition(
                listenerClass: RouteListener::class,
                target: new \ReflectionClass(ConsoleCommandInterface::class),
                scope: null,
            ))->getHash(),
        );
    }

    public function testGetHashWithScope(): void
    {
        $this->assertSame(
            '89b4dcb1f3b109c9d34118c01c4aad1c',
            (new ListenerDefinition(
                listenerClass: RouteListener::class,
                target: new \ReflectionClass(ConsoleCommandInterface::class),
                scope: 'scope',
            ))->getHash(),
        );
    }
}
