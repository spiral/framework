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
use Psr\Log\LoggerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\LogFactory;

final class MonologBootloader extends Bootloader implements Container\SingletonInterface
{
    protected const SINGLETONS = [
        LogsInterface::class   => LogFactory::class,
        LoggerInterface::class => Logger::class
    ];

    protected const BINDINGS = [
        'log.rotate' => [self::class, 'logRotate']
    ];

    /** @var ConfiguratorInterface */
    private $config;

    /**
     * @param ConfiguratorInterface $config
     */
    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        $this->config->setDefaults('monolog', [
            'globalLevel' => Logger::DEBUG,
            'handlers'    => []
        ]);

        $container->bindInjector(Logger::class, LogFactory::class);
    }

    /**
     * @param string           $channel
     * @param HandlerInterface $handler
     */
    public function addHandler(string $channel, HandlerInterface $handler): void
    {
        if (!isset($this->config->getConfig('monolog')['handlers'][$channel])) {
            $this->config->modify('monolog', new Append('handlers', $channel, []));
        }

        $this->config->modify('monolog', new Append(
            'handlers.' . $channel,
            null,
            $handler
        ));
    }

    /**
     * @param string $filename
     * @param int    $level
     * @param int    $maxFiles
     * @param bool   $bubble
     * @return HandlerInterface
     */
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
