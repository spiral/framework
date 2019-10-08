<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Http\Diactoros\ResponseFactory;
use Spiral\Http\Diactoros\ServerRequestFactory;
use Spiral\Http\Diactoros\StreamFactory;
use Spiral\Http\Diactoros\UploadedFileFactory;
use Spiral\Http\Diactoros\UriFactory;

/**
 * PSR-17 factories using Zend/Diactoros (default package).
 */
final class DiactorosBootloader extends Bootloader
{
    public const SINGLETONS = [
        ServerRequestFactoryInterface::class => ServerRequestFactory::class,
        ResponseFactoryInterface::class      => ResponseFactory::class,
        StreamFactoryInterface::class        => StreamFactory::class,
        UploadedFileFactoryInterface::class  => UploadedFileFactory::class,
        UriFactoryInterface::class           => UriFactory::class
    ];
}
