<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Boot\Exception\FatalException;
use Spiral\Exceptions\AbstractHandler;
use Spiral\Exceptions\ConsoleHandler;
use Spiral\Exceptions\HtmlHandler;

/**
 * ExceptionHandler is responsible for global error handling (outside of dispatchers). Handler
 * usually used in case of bootload errors.
 *
 * @codeCoverageIgnore
 */
final class ExceptionHandler
{
    /** @var resource */
    private static $output = STDERR;

    /**
     * @param resource $output
     */
    public static function setOutput($output): void
    {
        self::$output = $output;
    }

    /**
     * Enable global exception handling.
     */
    public static function register(): void
    {
        register_shutdown_function([self::class, 'handleShutdown']);
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    /**
     * Handle php shutdown and search for fatal errors.
     */
    public static function handleShutdown(): void
    {
        if (!empty($error = error_get_last())) {
            self::handleException(
                new FatalException(
                    $error['message'],
                    $error['type'],
                    0,
                    $error['file'],
                    $error['line']
                )
            );
        }
    }

    /**
     * Convert application error into exception.
     *
     * @param int    $code
     * @param string $message
     * @param string $filename
     * @param int    $line
     *
     * @throws \ErrorException
     */
    public static function handleError($code, $message, $filename = '', $line = 0): void
    {
        throw new \ErrorException($message, $code, 0, $filename, $line);
    }

    /**
     * Handle exception and output error to the user.
     *
     * @param \Throwable $e
     */
    public static function handleException(\Throwable $e): void
    {
        if (php_sapi_name() == 'cli') {
            $handler = new ConsoleHandler(self::$output);
        } else {
            $handler = new HtmlHandler(HtmlHandler::INVERTED);
        }

        // we are safe to handle global exceptions (system level) with maximum verbosity
        fwrite(self::$output, $handler->renderException($e, AbstractHandler::VERBOSITY_VERBOSE));
    }
}
