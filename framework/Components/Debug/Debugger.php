<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Debug;

use Spiral\Core\Component;
use Spiral\Core\Container;
use Spiral\Core\Core;
use Exception;
use Spiral\Core\Dispatcher\ClientException;

class Debugger extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait,
        Component\ConfigurableTrait,
        Component\LoggerTrait,
        Component\EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = 'debug';

    /**
     * Options for Debugger::dump() function to specify dump destination, default options are: return,
     * echo dump into log container "dumps".
     */
    const DUMP_ECHO     = 0;
    const DUMP_RETURN   = 1;
    const DUMP_LOG      = 2;
    const DUMP_LOG_NICE = 3;

    /**
     * Dump styles used to colorize and format variable dump performed using Debugger::dump or dump()
     * functions.
     *
     * @var array
     */
    private static $dumping = array(
        'maxLevel'  => 10,
        'container' => 'background-color: white; font-family: monospace;',
        'indent'    => '&middot;    ',
        'styles'    => array(
            'common'           => 'black',
            'name'             => 'black',
            'indent'           => 'gray',
            'indent-('         => 'black',
            'indent-)'         => 'black',
            'recursion'        => '#ff9900',
            'value-string'     => 'green',
            'value-integer'    => 'red',
            'value-double'     => 'red',
            'value-boolean'    => 'purple; font-weight: bold',
            'type'             => '#666',
            'type-object'      => '#333',
            'type-array'       => '#333',
            'type-null'        => '#666; font-weight: bold',
            'type-resource'    => '#666; font-weight: bold',
            'access'           => '#666',
            'access-public'    => '#8dc17d',
            'access-private'   => '#c18c7d',
            'access-protected' => '#7d95c1'
        )
    );

    /**
     * If enabled Debugger::benchmark() method will collect all benchmarks into Debugger::$benchmarks
     * property. Benchmarks can retrieved using Debugger::getBenchmarks() method. Only records from
     * current script session and recorded after option got enabled will be collection in benchmarks
     * array.
     *
     * @var bool
     */
    protected static $benchmarking = false;

    /**
     * Benchmarking. You can use Debugger::benchmark('record') to start and stop profiling for some
     * operations. Multiple spiral components already have benchmarking mounted, however benchmarking
     * is disabled by default and be enabled by Debugger::benchmarking() method. Benchmarks can
     * retrieved using Debugger::getBenchmarks() method.
     *
     * @var array
     */
    protected static $benchmarks = array();

    /**
     * Constructing debug component. Debug is one of primary spiral component and will be available
     * for use in any environment and any application point. This is first initiated component in
     * application.
     *
     * @param Core $core
     */
    public function __construct(Core $core)
    {
        $this->config = $core->loadConfig('debug');
    }

    /**
     * Get list of file handlers associated with specified container.
     *
     * @param string $container
     * @return array
     */
    public function getFileHandlers($container)
    {
        return isset($this->config['loggers']['containers'][$container])
            ? $this->config['loggers']['containers'][$container]
            : array();
    }

    /**
     * If enabled Debugger::benchmark() method will collect all benchmarks into Debugger::$benchmarks
     * property. Benchmarks can retrieved using Debugger::getBenchmarks() method. Only records from
     * current script session and recorded after option got enabled will be collection in benchmarks
     * array.
     *
     * @param bool $enabled
     * @return bool
     */
    public static function benchmarking($enabled = true)
    {
        $currentValue = self::$benchmarking;
        self::$benchmarking = $enabled;

        return $currentValue;
    }

    /**
     * Benchmark method used to determinate how long time and how much memory was used to perform
     * some specified piece of code. Method should be used twice, before and after code needs to be
     * profile, first call will return true, second one will return time in seconds took to perform
     * code between benchmark method calls. If Debugger::$benchmarking enabled - result will be
     * additionally logged in Debugger::$benchmarks array and can be retrieved using
     * Debugger::getBenchmarks() for future analysis.
     *
     * Example:
     * Debugger::benchmark('parseURL', 'google.com');
     * ...
     * echo Debugger::benchmark('parseURL', 'google.com');
     *
     * @param string $record Record name.
     * @return bool|float
     */
    public static function benchmark($record)
    {
        if (func_num_args() > 1)
        {
            $record = join('|', func_get_args());
        }

        if (!isset(self::$benchmarks[$record]))
        {
            self::$benchmarks[$record] = array(
                microtime(true),
                memory_get_usage()
            );

            return true;
        }

        self::$benchmarks[$record][] = microtime(true);
        self::$benchmarks[$record][] = memory_get_usage();

        $result = self::$benchmarks[$record][2] - self::$benchmarks[$record][0];

        if (!self::$benchmarking)
        {
            unset(self::$benchmarks[$record]);
        }

        return $result;
    }

    /**
     * Retrieve all active and finished benchmark records, this method will return finished records
     * only if Debugger::$benchmarking is true, in opposite case all finished records will be erased
     * right after completion.
     *
     * @return array|null
     */
    public static function getBenchmarks()
    {
        return self::$benchmarks;
    }

    /**
     * Will convert Exception to ExceptionResponse object which can be passed further to dispatcher
     * and handled by environment logic. Additionally error message will be recorded in "error" debug
     * container.
     *
     * ExceptionResponse will contain full exception explanation and rendered snapshot which can be
     * recorded as html file for future usage.
     *
     * @param Exception $exception
     * @param bool      $logException If true (default), message to error container will be added.
     * @return Snapshot
     */
    public function handleException(Exception $exception, $logException = true)
    {
        $snapshot = Snapshot::make(array(
            'exception' => $exception,
            'view'      => $this->config['backtrace']['view'],
            'config'    => $this->config['backtrace']['snapshots']
        ));

        //Letting subscribers know...
        $this->event('snapshot', $snapshot);

        if ($exception instanceof ClientException)
        {
            //No logging for ClientExceptions
            return $snapshot;
        }

        //Error message should be added to log only for non http exceptions
        if ($logException)
        {
            self::logger()->error($snapshot->getMessage());
        }

        return $snapshot;
    }

    /**
     * Helper function to dump variable into specified destination (output, log or return) using
     * pre-defined dumping styles. This method is fairly slow and should not be used in productions
     * environment. Only use it during development, error handling and other not high loaded
     * application parts.
     *
     * Method has alias with short function dump() which is always defined.
     *
     * @param mixed $value  Value to be dumped.
     * @param int   $output Output method, can print, return or log value dump.
     * @return null|string
     */
    public static function dump($value, $output = self::DUMP_ECHO)
    {
        if (Core::isConsole() && $output != self::DUMP_LOG)
        {
            print_r($value);

            return null;
        }

        $result = "<pre style='" . self::$dumping['container'] . "'>"
            . self::dumpVariable($value, '', 0)
            . "</pre>";

        switch ($output)
        {
            case self::DUMP_ECHO:
                echo $result;
                break;

            case self::DUMP_RETURN:
                return $result;

            case self::DUMP_LOG:
                self::logger()->debug(print_r($value, true));
                break;

            case self::DUMP_LOG_NICE:
                self::logger()->debug(self::dump($value, self::DUMP_RETURN));
                break;
        }

        return null;
    }

    /**
     * Variable dumping method, can be called recursively, maximum nested level specified in
     * Debugger::$dumping['maxLevel']. Dumper values, braces and other parts will be styled using
     * rules defined in Debugger::$dumping. Styles can be redefined any moment. You can hide class
     * fields from dumping by using @invisible doc comment option.
     *
     * This is the oldest spiral function, it was originally written in 2008. :)
     *
     * @param mixed  $variable Value to be dumped.
     * @param string $name     Current variable name (empty if no name).
     * @param int    $level
     * @param bool   $hideType True to hide object/array type declaration, used by __debugInfo.
     * @return string
     */
    private static function dumpVariable($variable, $name = '', $level = 0, $hideType = false)
    {
        $result = $indent = self::getIndent($level);
        if (!$hideType && $name)
        {
            $result .= self::getStyle($name, "name") . self::getStyle(" = ", "indent", "equal");
        }

        if ($level > self::$dumping['maxLevel'])
        {
            return $indent . self::getStyle('-possible recursion-', 'recursion') . "\n";
        }

        $type = strtolower(gettype($variable));

        if ($type == 'array')
        {
            return $result . self::dumpArray($variable, $level, $hideType);
        }

        if ($type == 'object')
        {
            return $result . self::dumpObject($variable, $level, $hideType);
        }

        if ($type == 'resource')
        {
            $result .= self::getStyle(
                    get_resource_type($variable) . " resource ",
                    "type",
                    "resource"
                ) . "\n";

            return $result;
        }

        $result .= self::getStyle($type . "(" . strlen($variable) . ")", "type", $type);

        $value = null;
        switch ($type)
        {
            case "string":
                $value = htmlspecialchars($variable);
                break;

            case "boolean":
                $value = ($variable ? "true" : "false");
                break;

            default:
                if ($variable !== null)
                {
                    //Not showing null value, type is enough
                    $value = var_export($variable, true);
                }
        }

        return $result . " " . self::getStyle($value, "value", $type) . "\n";
    }

    /**
     * Helper method used to arrays.
     *
     * @param mixed $variable Value to be dumped.
     * @param int   $level
     * @param bool  $hideType True to hide object/array type declaration, used by __debugInfo.
     * @return string
     */
    private static function dumpArray($variable, $level, $hideType)
    {
        $result = '';
        $indent = self::getIndent($level);
        if (!$hideType)
        {
            $count = count($variable);
            $result .= self::getStyle("array({$count})", "type", "array")
                . "\n" . $indent . self::getStyle("(", "indent", "(") . "\n";
        }

        foreach ($variable as $name => $value)
        {
            if (!is_numeric($name))
            {
                if (is_string($name))
                {
                    $name = htmlspecialchars($name);
                }
                $name = "'" . $name . "'";
            }

            $result .= self::dumpVariable(
                $value,
                "[{$name}]",
                $level + 1
            );
        }

        if (!$hideType)
        {
            $result .= $indent . self::getStyle(")", "indent", ")") . "\n";
        }

        return $result;
    }

    /**
     * Helper method used to dump objects.
     *
     * @param mixed $variable Value to be dumped.
     * @param int   $level
     * @param bool  $hideType True to hide object/array type declaration, used by __debugInfo.
     * @return string
     */
    private static function dumpObject($variable, $level, $hideType)
    {
        $result = '';
        $indent = self::getIndent($level);
        if (!$hideType)
        {
            $type = get_class($variable) . " object ";
            $result .= self::getStyle($type, "type", "object") .
                "\n" . $indent . self::getStyle("(", "indent", "(") . "\n";
        }

        if (method_exists($variable, '__debugInfo'))
        {
            $result .= self::dumpVariable(
                $variable = $variable->__debugInfo(),
                '',
                $level + (is_scalar($variable)),
                true
            );

            if ($hideType)
            {
                return $result;
            }

            return $result . $indent . self::getStyle(")", "parentheses") . "\n";
        }

        $refection = new \ReflectionObject($variable);
        foreach ($refection->getProperties() as $property)
        {
            if ($property->isStatic())
            {
                continue;
            }

            //Memory loop while reading doc comment for stdClass variables?
            if (!($variable instanceof \stdClass) && strpos($property->getDocComment(), '@invisible'))
            {
                continue;
            }

            $access = "public";
            if ($property->isPrivate())
            {
                $access = "private";
            }
            elseif ($property->isProtected())
            {
                $access = "protected";
            }
            $property->setAccessible(true);

            if ($variable instanceof \stdClass)
            {
                $access = 'dynamic';
            }

            $value = $property->getValue($variable);
            $result .= self::dumpVariable(
                $value,
                $property->getName() . self::getStyle(":" . $access, "access", $access),
                $level + 1
            );
        }

        return $result . $indent . self::getStyle(")", "parentheses") . "\n";
    }

    /**
     * Indent based on variable level.
     *
     * @param int $level
     * @return string
     */
    private static function getIndent($level)
    {
        if (!$level)
        {
            return '';
        }

        return self::getStyle(str_repeat(self::$dumping["indent"], $level), "indent");
    }

    /**
     * Update dumping styles.
     *
     * @param array $styles
     */
    public static function dumpingStyles(array $styles)
    {
        self::$dumping = $styles;
    }

    /**
     * Stylize content using pre-defined style. Dump styles defined in Debugger::$dumping and can be
     * redefined at any moment.
     *
     * @param string $content Content to apply style to.
     * @param string $type    Content type (value, indent, name and etc)
     * @param string $subType Content sub type (int, string and etc...)
     * @return string
     */
    public static function getStyle($content, $type, $subType = '')
    {
        if (isset(self::$dumping['styles'][$type . '-' . $subType]))
        {
            $style = self::$dumping['styles'][$type . '-' . $subType];
        }
        elseif (isset(self::$dumping['styles'][$type]))
        {
            $style = self::$dumping['styles'][$type];
        }
        else
        {
            $style = self::$dumping['styles']['common'];
        }

        if (!empty($style))
        {
            $content = "<span style='color: {$style};'>$content</span>";
        }

        return $content;
    }
}