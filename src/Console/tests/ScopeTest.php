<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Exception\ScopeException;
use Spiral\Tests\Console\Fixtures\User\UserCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ScopeTest extends TestCase
{
    public function testScopeError(): void
    {
        $this->expectException(ScopeException::class);

        $c = new UserCommand();
        $c->run(new ArrayInput([]), new BufferedOutput());
    }
}
