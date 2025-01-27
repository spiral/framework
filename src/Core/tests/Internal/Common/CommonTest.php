<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Common;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Core\Exception\Resolver\WrongTypeException;
use Spiral\Core\Options;
use Spiral\Tests\Core\Fixtures\Bucket;

final class CommonTest extends TestCase
{
    public function testDisableArgumentsValidationWithFactory(): void
    {
        $options = new Options();
        $options->validateArguments = false;
        $container = new Container(options: $options);

        $this->expectException(WrongTypeException::class);

        $container->make(Bucket::class, [
            'name' => 123,
        ]);
    }

    public function testEnableArgumentsValidationWithFactory(): void
    {
        $options = new Options();
        $options->validateArguments = true;
        $container = new Container(options: $options);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessageMatches('/Can\'t resolve /');
        $this->expectExceptionMessageMatches('/Invalid argument value type for the `name`/');

        $container->make(Bucket::class, [
            'name' => 123,
        ]);
    }

    public function testDisableArgumentsValidationWithInvoker(): void
    {
        $options = new Options();
        $options->validateArguments = false;
        $container = new Container(options: $options);

        $this->expectException(\TypeError::class);

        $container->invoke(static fn(int $x): int => $x, [
            'x' => 'string',
        ]);
    }

    public function testEnableArgumentsValidationWithInvoker(): void
    {
        $options = new Options();
        $options->validateArguments = true;
        $container = new Container(options: $options);

        $this->expectException(InvalidArgumentException::class);

        $container->invoke(static fn(int $x): int => $x, [
            'x' => 'string',
        ]);
    }
}
