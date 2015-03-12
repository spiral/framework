<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Debug;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;

class Logger extends Component implements LoggerInterface
{
    /**
     * Events for logging.
     */
    use Component\EventsTrait;

    /**
     * Helper constant to associate all log levels with one filename.
     */
    const ALL_MESSAGES = 'all';

    /**
     * Default logging name (channel).
     */
    const DEFAULT_NAME = 'debug';

    /**
     * Time format and postfix for rotated files. "all" format will be used for messages going to
     * container assigned to ALL_MESSAGES (without level).
     *
     * @var array
     */
    protected $options = array(
        'all'        => '{date}: [{level}] {message}',
        'level'      => '{date}: {message}',
        'dateFormat' => 'H:i:s d.m.Y',
        'oldPostfix' => '.old'
    );

    /**
     * All created or registered loggers.
     *
     * @var array
     */
    public static $loggers = array();

    /**
     * If enabled all debug messages will be additionally collected in Logger::$logMessages array for
     * future analysis. Only messages from current script session and recorded after option got
     * enabled will be collected.
     *
     * @var bool
     */
    protected static $memoryLogging = true;

    /**
     * Log messages collected during application runtime. Messages will be displayed in exception
     * snapshot or can be retrieved by profiler module, memory logging disabled by CLI dispatched in
     * console environment.
     *
     * @var array
     */
    protected static $logMessages = array();

    /**
     * Logging container name, usually defined by component alias or class name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * List of log levels associated with target filenames and filesizes, messages matched to level
     * conditions will be immediately recorded in specified file. If file size will exceed provided
     * number, file will be automatically rotated
     * using postfix.
     *
     * @var array
     */
    protected $fileHandlers = array();

    /**
     * Logger request ID, can be enabled by changing log message format (include {reqID}), this value
     * can be useful to separate log messages raised by different clients at the same time.
     *
     * @var string
     */
    protected static $reqID = '';

    /**
     * New logger instance, usually attached to component or set of models, by model class name or
     * alias. PSR-3 compatible and can be replaced with foreign implementation. File handlers
     * configuration will be fetched from debug component.
     *
     * @param Debugger $debugger Debugger component.
     * @param string   $name     Channel name (usually component alias).
     */
    public function __construct(Debugger $debugger, $name = self::DEFAULT_NAME)
    {
        $this->name = $name;
        $this->fileHandlers = $debugger->getFileHandlers($name);

        if (!self::$reqID)
        {
            self::$reqID = hash(
                'crc32b',
                uniqid() . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'CLI')
            );
        }
    }

    /**
     * Get logger name (channel).
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get logger options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Change logger options, like date formats, rotations postfixes and etc.
     *
     * @param array $options
     * @return static
     */
    public function setOptions($options)
    {
        $this->options = $options + $this->options;

        return $this;
    }

    /**
     * Add file handler to output all log messages with specified log level, if log level specified
     * as Logger::ALL_MESSAGES every message will be dumped to that file, however if there is more
     * specific log level handler - it will be used instead of "all" handler.
     *
     * @param string $level    Log level, use Logger::allMessages to log all messages.
     * @param string $filename Log filename.
     * @param int    $filesize Max filesize to perform rotation, 2MB by default.
     * @return static
     */
    public function setFileHandler($level, $filename, $filesize = 2097152)
    {
        $this->fileHandlers[$level] = array($filename, $filesize);

        return $this;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function emergency($message, array $context = array())
    {
        return $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts
     * and wake you up.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function alert($message, array $context = array())
    {
        return $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function critical($message, array $context = array())
    {
        return $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function error($message, array $context = array())
    {
        return $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily
     * wrong.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function warning($message, array $context = array())
    {
        return $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function notice($message, array $context = array())
    {
        return $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function info($message, array $context = array())
    {
        return $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function debug($message, array $context = array())
    {
        return $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with specified level. If logger has defined file handlers message will be automatically
     * written to file.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     * @return static
     */
    public function log($level, $message, array $context = array())
    {
        $message = interpolate($message, $context);
        if (self::$memoryLogging)
        {
            self::$logMessages[] = array($this->name, microtime(true), $level, $message, $context);
        }

        $handled = $this->event('message', array(
            'message' => $message,
            'name'    => $this->name,
            'level'   => $level
        ));

        if (empty($handled))
        {
            return $this;
        }

        if (isset($this->fileHandlers[$level]))
        {
            list($filename, $filesize) = $this->fileHandlers[$level];
            $format = $this->options['level'];
        }
        elseif (isset($this->fileHandlers[self::ALL_MESSAGES]))
        {
            list($filename, $filesize) = $this->fileHandlers[$level];
            $format = $this->options[self::ALL_MESSAGES];
        }
        else
        {
            return $this;
        }

        $message = interpolate(
            $format,
            array(
                'message' => $message,
                'level'   => $level,
                'date'    => date($this->options['dateFormat'], time()),
                'reqID'   => self::$reqID
            )
        );

        $files = FileManager::getInstance();
        if ($files->append($filename, $message . PHP_EOL, FileManager::RUNTIME, true))
        {
            if ($files->size($filename) > $filesize)
            {
                $files->move($filename, $filename . $this->options['rotatePostfix']);
            }
        }

        return $this;
    }

    /**
     * If enabled all debug messages will be additionally collected in $logMessages array for future
     * analysis. Only messages from current script session and recorded after option got enabled will
     * be collection in logMessages array.
     *
     * @param bool $enabled
     * @return bool
     */
    public static function memoryLogging($enabled = true)
    {
        $currentValue = self::$memoryLogging;
        self::$memoryLogging = $enabled;

        return $currentValue;
    }

    /**
     * Get all recorded log messages.
     *
     * @return array
     */
    public static function logMessages()
    {
        return self::$logMessages;
    }
}