<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Component;

use Psr\Log\LoggerInterface;
use Spiral\Components\Debug\Logger;
use Spiral\Core\Container;

trait LoggerTrait
{
    /**
     * Sets a logger. Logger will be associated with specific component by it's alias.
     *
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        Logger::setLogger(static::class, $logger);
    }

    /**
     * Logger instance which is currently attached to component implementation, can be redefined
     * using setLogger() method. LoggerInterface instance will be created on demand and depends on
     * "logger" binding in spiral core. Every new LoggerInterface will receive "name" argument which
     * is equal to getAlias() method result and declares logging channel.
     *
     * If no "logger" binding presented, default logger will be used (performance reasons).
     *
     * @return LoggerInterface|Logger
     */
    public static function logger()
    {
        return Logger::getLogger(static::class);
    }
}