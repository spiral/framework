<?php

declare(strict_types=1);

namespace Spiral\Http\Bootloader;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Boot\Bootloader\Bootloader;

/**
 * PSR-17 factories using Laminas/Diactoros (default package).
 */
final class DiactorosBootloader extends Bootloader
{
    protected const SINGLETONS = [
        ServerRequestFactoryInterface::class => Psr17Factory::class,
        ResponseFactoryInterface::class      => Psr17Factory::class,
        StreamFactoryInterface::class        => Psr17Factory::class,
        UploadedFileFactoryInterface::class  => Psr17Factory::class,
        UriFactoryInterface::class           => Psr17Factory::class,
    ];
}
