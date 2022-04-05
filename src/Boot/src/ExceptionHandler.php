<?php

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Boot\Exception\FatalException;
use Spiral\Exceptions\Renderer\AbstractRenderer;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\HtmlRenderer;

/**
 * ExceptionHandler is responsible for global error handling (outside of dispatchers). Handler
 * usually used in case of bootload errors.
 *
 * @codeCoverageIgnore
 */
final class ExceptionHandler
{
    /** @var resource */
    private static mixed $output = null;

    /**
     * @param resource $output
     */
    public static function setOutput(mixed $output): void
    {
        self::$output = $output;
    }

    /**
     * Enable global exception handling.
     */
    public static function register(): void
    {
        \register_shutdown_function([self::class, 'handleShutdown']);
        \set_error_handler([self::class, 'handleError']);
        \set_exception_handler([self::class, 'handleException']);
    }

    /**
     * Handle php shutdown and search for fatal errors.
     */
    public static function handleShutdown(): void
    {
        if (!empty($error = \error_get_last())) {
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
     * @throws \ErrorException
     */
    public static function handleError(int $code, string $message, string $filename = '', int $line = 0): void
    {
        if (!(\error_reporting() & $code)) {
            return;
        }

        throw new \ErrorException($message, $code, 0, $filename, $line);
    }

    /**
     * Handle exception and output error to the user.
     */
    public static function handleException(\Throwable $e): void
    {
        if (self::$output === null) {
            self::$output = \defined('STDERR') ? STDERR : \fopen('php://stderr', 'w+');
        }

        if (\php_sapi_name() === 'cli') {
            $handler = new ConsoleRenderer(self::$output);
        } else {
            $handler = new HtmlRenderer(HtmlRenderer::INVERTED);
        }

        // we are safe to handle global exceptions (system level) with maximum verbosity
        \fwrite(self::$output, $handler->renderException($e, AbstractRenderer::VERBOSITY_VERBOSE));
    }
}
