<?php

declare(strict_types=1);

namespace Spiral\Monolog\Bootloader;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\ResettableInterface;
use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Container;
use Spiral\Logger\Bootloader\LoggerBootloader;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\LogFactory;

#[Singleton]
final class MonologBootloader extends Bootloader
{
    protected const SINGLETONS = [
        LogsInterface::class => LogFactory::class,
        Logger::class => Logger::class,
    ];

    protected const BINDINGS = [
        'log.rotate' => [self::class, 'logRotate'],
    ];

    protected const DEPENDENCIES = [
        LoggerBootloader::class,
    ];

    private const DEFAULT_FORMAT = "[%datetime%] %level_name%: %message% %context%\n";

    public function __construct(
        private readonly ConfiguratorInterface $config,
        private readonly EnvironmentInterface $env,
    ) {
    }

    public function init(Container $container, FinalizerInterface $finalizer): void
    {
        $finalizer->addFinalizer(static function (bool $terminate) use ($container): void {
            if ($terminate) {
                return;
            }

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
            'default' => $this->env->get('MONOLOG_DEFAULT_CHANNEL', MonologConfig::DEFAULT_CHANNEL),
            'globalLevel' => Logger::DEBUG,
            'handlers' => [],
        ]);
    }

    public function addHandler(string $channel, HandlerInterface $handler): void
    {
        if (!isset($this->config->getConfig(MonologConfig::CONFIG)['handlers'][$channel])) {
            $this->config->modify(MonologConfig::CONFIG, new Append('handlers', $channel, []));
        }

        $this->config->modify(
            MonologConfig::CONFIG,
            new Append(
                'handlers.' . $channel,
                null,
                $handler
            )
        );
    }

    public function logRotate(
        string $filename,
        int|Level $level = Logger::DEBUG,
        int $maxFiles = 0,
        bool $bubble = true
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
            new LineFormatter($this->env->get('MONOLOG_FORMAT', self::DEFAULT_FORMAT))
        );
    }
}
