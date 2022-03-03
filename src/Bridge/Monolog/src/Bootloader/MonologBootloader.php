<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Monolog\Bootloader;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\ResettableInterface;
use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\LogFactory;

final class MonologBootloader extends Bootloader implements Container\SingletonInterface
{
    protected const SINGLETONS = [
        LogsInterface::class => LogFactory::class,
        LoggerInterface::class => Logger::class,
    ];

    protected const BINDINGS = [
        'log.rotate' => [self::class, 'logRotate'],
    ];

    /** @var ConfiguratorInterface */
    private $config;

    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    public function boot(Container $container, FinalizerInterface $finalizer): void
    {
        $finalizer->addFinalizer(static function () use ($container): void {
            if ($container->hasInstance(LoggerInterface::class)) {
                $logger = $container->get(LoggerInterface::class);

                if ($logger instanceof ResettableInterface) {
                    $logger->reset();
                }
            }

            if ($container->hasInstance(LogsInterface::class)) {
                $factory = $container->get(LogsInterface::class);

                if ($factory instanceof ResettableInterface) {
                    $factory->reset();
                }
            }
        });

        $this->config->setDefaults(MonologConfig::CONFIG, [
            'globalLevel' => Logger::DEBUG,
            'handlers' => [],
        ]);

        $container->bindInjector(Logger::class, LogFactory::class);
    }

    public function addHandler(string $channel, HandlerInterface $handler): void
    {
        $name = MonologConfig::CONFIG;

        if (!isset($this->config->getConfig($name)['handlers'][$channel])) {
            $this->config->modify($name, new Append('handlers', $channel, []));
        }

        $this->config->modify(
            $name,
            new Append(
                'handlers.' . $channel,
                null,
                $handler
            )
        );
    }

    public function logRotate(
        string $filename,
        int $level = Logger::DEBUG,
        int $maxFiles = 0,
        bool $bubble = false
    ): HandlerInterface {
        $handler = new RotatingFileHandler(
            $filename,
            $maxFiles,
            $level,
            $bubble,
            null,
            true
        );

        return $handler->setFormatter(
            new LineFormatter("[%datetime%] %level_name%: %message% %context%\n")
        );
    }
}
