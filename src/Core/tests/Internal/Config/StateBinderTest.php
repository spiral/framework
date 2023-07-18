<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Exception\Binder\DuplicateBindingException;
use Spiral\Core\Internal\Config\StateBinder;
use Spiral\Core\Internal\State;

final class StateBinderTest extends TestCase
{
    public function testBindSingletonException(): void
    {
        $binder = new StateBinder(new State());

        $binder->bindSingleton('test', new \stdClass());

        $this->expectException(DuplicateBindingException::class);
        $binder->bindSingleton('test', new \stdClass());
    }
}
