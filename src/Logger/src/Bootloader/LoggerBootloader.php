<?php

declare(strict_types=1);

namespace Spiral\Logger\Bootloader;

use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Logger\Attribute\LoggerChannel;
use Spiral\Logger\LogFactory;
use Spiral\Logger\LoggerInjector;
use Spiral\Logger\LogsInterface;
use Spiral\Logger\NullLogger;

/**
 * Register {@see LoggerInterface} injector with support for {@see LoggerChannel} attribute.
 * Register default {@see LogsInterface} implementation that produces {@see NullLogger}.
 */
final class LoggerBootloader extends Bootloader
{
    protected const SINGLETONS = [
        LogsInterface::class => LogFactory::class,
    ];

    public function init(Container $container): void
    {
        $container->bindInjector(LoggerInterface::class, LoggerInjector::class);
    }
}
