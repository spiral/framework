<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Debug;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Container;

class Logger extends AbstractLogger
{
    /**
     * Events for logging.
     */
    use Component\MakeTrait, Component\EventsTrait;

    /**
     * Helper constant to associate all log levels with one filename.
     */
    const ALL_MESSAGES = 'all';

    /**
     * Default logging name (channel).
     */
    const DEFAULT_NAME = 'debug';

    /**
     * Message parts (stored in static log container).
     */
    const MESSAGE_CONTAINER = 0;
    const MESSAGE_TIMESTAMP = 1;
    const MESSAGE_LEVEL     = 2;
    const MESSAGE_BODY      = 3;
    const MESSAGE_CONTEXT   = 4;

    /**
     * Time format and postfix for rotated files. "all" format will be used for messages going to
     * container assigned to ALL_MESSAGES (without level).
     *
     * @var array
     */
    protected $options = [
        'all'        => '{date}: [{level}] {message}',
        'level'      => '{date}: {message}',
        'dateFormat' => 'H:i:s d.m.Y',
        'oldPostfix' => '.old'
    ];

    /**
     * All created or registered loggers.
     *
     * @var array
     */
    protected static $loggers = [];

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
    protected static $logMessages = [];

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
    protected $fileHandlers = [];

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
        $this->fileHandlers = $debugger->getLogHandlers($name);

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
        $this->fileHandlers[$level] = [$filename, $filesize];

        return $this;
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
    public function log($level, $message, array $context = [])
    {
        $message = interpolate($message, $context);
        if (self::$memoryLogging)
        {
            self::$logMessages[] = [
                self::MESSAGE_CONTAINER => $this->name,
                self::MESSAGE_TIMESTAMP => microtime(true),
                self::MESSAGE_LEVEL     => $level,
                self::MESSAGE_BODY      => $message,
                self::MESSAGE_CONTEXT   => $context
            ];
        }

        $handled = $this->event('message', [
            'message' => $message,
            'name'    => $this->name,
            'level'   => $level
        ]);

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
            list($filename, $filesize) = $this->fileHandlers[self::ALL_MESSAGES];
            $format = $this->options[self::ALL_MESSAGES];
        }
        else
        {
            return $this;
        }

        $message = interpolate(
            $format,
            [
                'message' => $message,
                'level'   => $level,
                'date'    => date($this->options['dateFormat'], time()),
                'reqID'   => self::$reqID
            ]
        );

        $files = FileManager::getInstance();
        if ($files->append($filename, $message . PHP_EOL, FileManager::RUNTIME, true))
        {
            if ($files->size($filename) > $filesize)
            {
                $files->move($filename, $filename . $this->options['oldPostfix']);
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

    /**
     * Pre-define logger for specified component.
     *
     * @param string          $name Component alias.
     * @param LoggerInterface $logger
     */
    public static function setLogger($name, LoggerInterface $logger)
    {
        self::$loggers[$name] = $logger;
    }

    /**
     * Get instance of logger for specified component. By default instance of Logger will be created,
     * this behaviour can be redefined via binding "logger" in Container.
     *
     * @param string $name Component alias.
     * @return mixed|null|object|static
     */
    public static function getLogger($name)
    {
        if (isset(self::$loggers[$name]))
        {
            return self::$loggers[$name];
        }

        $container = Container::getInstance();
        if (!$container->hasBinding('logger'))
        {
            return self::$loggers[$name] = new static(Debugger::getInstance($container), $name);
        }

        return self::$loggers[$name] = $container->get('logger', compact('name'));
    }
}