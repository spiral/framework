<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Spiral\Debug\Exception\DumperException;
use Spiral\Debug\Renderer\ConsoleRenderer;
use Spiral\Debug\Renderer\HtmlRenderer;
use Spiral\Debug\Renderer\PlainRenderer;

/**
 * Renderer exports the content of the given variable, array or object into human friendly form.
 */
class Dumper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Directives for dump output forwarding.
     */
    public const OUTPUT            = 0;
    public const RETURN            = 1;
    public const LOGGER            = 2;
    public const ERROR_LOG         = 3;
    public const OUTPUT_CLI        = 4;
    public const OUTPUT_CLI_COLORS = 5;

    /** @var int */
    private $maxLevel = 12;

    /**
     * Default render associations.
     *
     * @var array|RendererInterface[]
     */
    private $targets = [
        self::OUTPUT            => HtmlRenderer::class,
        self::OUTPUT_CLI        => PlainRenderer::class,
        self::OUTPUT_CLI_COLORS => ConsoleRenderer::class,
        self::RETURN            => HtmlRenderer::class,
        self::LOGGER            => PlainRenderer::class,
        self::ERROR_LOG         => PlainRenderer::class,
    ];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        if (!empty($logger)) {
            $this->setLogger($logger);
        }
    }

    /**
     * Set max nesting level for value dumping.
     *
     * @param int $maxLevel
     */
    public function setMaxLevel(int $maxLevel): void
    {
        $this->maxLevel = max($maxLevel, 1);
    }

    /**
     * Dump given value into target output.
     *
     * @param mixed $value
     * @param int   $target Possible options: OUTPUT, RETURN, ERROR_LOG, LOGGER.
     * @return string
     * @throws DumperException
     */
    public function dump($value, int $target = self::OUTPUT): ?string
    {
        $r = $this->getRenderer($target);
        $dump = $r->wrapContent($this->renderValue($r, $value));

        switch ($target) {
            case self::OUTPUT:
                echo $dump;
                break;

            case self::RETURN:
                return $dump;

            case self::LOGGER:
                if ($this->logger == null) {
                    throw new DumperException('Unable to dump value to log, no associated LoggerInterface');
                }
                $this->logger->debug($dump);
                break;

            case self::ERROR_LOG:
                error_log($dump, 0);
                break;
        }

        return null;
    }

    /**
     * Associate rendered with given output target.
     *
     * @param int               $target
     * @param RendererInterface $renderer
     * @return Dumper
     * @throws DumperException
     */
    public function setRenderer(int $target, RendererInterface $renderer): Dumper
    {
        if (!isset($this->targets[$target])) {
            throw new DumperException(sprintf('Undefined dump target %d', $target));
        }

        $this->targets[$target] = $renderer;

        return $this;
    }

    /**
     * Returns renderer instance associated with given output target. Automatically detects CLI mode, RR mode and
     * colorization support.
     *
     * @param int $target
     * @return RendererInterface
     * @throws DumperException
     */
    private function getRenderer(int $target): RendererInterface
    {
        if ($target == self::OUTPUT && System::isCLI()) {
            if (System::isColorsSupported(STDOUT)) {
                $target = self::OUTPUT_CLI_COLORS;
            } else {
                $target = self::OUTPUT_CLI;
            }
        }

        if (!isset($this->targets[$target])) {
            throw new DumperException(sprintf('Undefined dump target %d', $target));
        }

        if (is_string($this->targets[$target])) {
            $this->targets[$target] = new $this->targets[$target]();
        }

        return $this->targets[$target];
    }

    /**
     * Variable dumper. This is the oldest spiral function originally written in 2007. :).
     *
     * @param RendererInterface $r          Render to style value content.
     * @param mixed             $value
     * @param string            $name       Variable name, internal.
     * @param int               $level      Dumping level, internal.
     * @param bool              $hideHeader Hide array/object header, internal.
     *
     * @return string
     */
    private function renderValue(
        RendererInterface $r,
        $value,
        string $name = '',
        int $level = 0,
        bool $hideHeader = false
    ): string {
        if (!$hideHeader && !empty($name)) {
            $header = $r->indent($level) . $r->apply($name, 'name') . $r->apply(' = ', 'syntax', '=');
        } else {
            $header = $r->indent($level);
        }

        if ($level > $this->maxLevel) {
            //Renderer is not reference based, we can't dump too deep values
            return $r->indent($level) . $r->apply('-too deep-', 'maxLevel') . "\n";
        }

        $type = strtolower(gettype($value));

        if ($type == 'array') {
            return $header . $this->renderArray($r, $value, $level, $hideHeader);
        }

        if ($type == 'object') {
            return $header . $this->renderObject($r, $value, $level, $hideHeader);
        }

        if ($type == 'resource') {
            //No need to dump resource value
            $element = get_resource_type($value) . ' resource ';

            return $header . $r->apply($element, 'type', 'resource') . "\n";
        }

        //Value length
        $length = strlen((string)$value);

        //Including type size
        $header .= $r->apply("{$type}({$length})", 'type', $type);

        $element = null;
        switch ($type) {
            case 'string':
                $element = $r->escapeStrings() ? htmlspecialchars($value) : $value;
                break;

            case 'boolean':
                $element = ($value ? 'true' : 'false');
                break;

            default:
                if ($value !== null) {
                    //Not showing null value, type is enough
                    $element = var_export($value, true);
                }
        }

        //Including value
        return $header . ' ' . $r->apply($element, 'value', $type) . "\n";
    }

    /**
     * @param RendererInterface $r
     * @param array             $array
     * @param int               $level
     * @param bool              $hideHeader
     *
     * @return string
     */
    private function renderArray(RendererInterface $r, array $array, int $level, bool $hideHeader = false): string
    {
        if (!$hideHeader) {
            $count = count($array);

            //Array size and scope
            $output = $r->apply("array({$count})", 'type', 'array') . "\n";
            $output .= $r->indent($level) . $r->apply('[', 'syntax', '[') . "\n";
        } else {
            $output = '';
        }

        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                if (is_string($key) && $r->escapeStrings()) {
                    $key = htmlspecialchars($key);
                }

                $key = "'{$key}'";
            }

            $output .= $this->renderValue($r, $value, "[{$key}]", $level + 1);
        }

        if (!$hideHeader) {
            //Closing array scope
            $output .= $r->indent($level) . $r->apply(']', 'syntax', ']') . "\n";
        }

        return $output;
    }

    /**
     * @param RendererInterface $r
     * @param object            $value
     * @param int               $level
     * @param bool              $hideHeader
     * @param string            $class
     *
     * @return string
     */
    private function renderObject(
        RendererInterface $r,
        $value,
        int $level,
        bool $hideHeader = false,
        string $class = ''
    ): string {
        if (!$hideHeader) {
            $type = ($class ?: get_class($value)) . ' object ';

            $header = $r->apply($type, 'type', 'object') . "\n";
            $header .= $r->indent($level) . $r->apply('(', 'syntax', '(') . "\n";
        } else {
            $header = '';
        }

        //Let's use method specifically created for dumping
        if (method_exists($value, '__debugInfo') || $value instanceof \Closure) {
            if ($value instanceof \Closure) {
                $debugInfo = $this->describeClosure($value);
            } else {
                $debugInfo = $value->__debugInfo();
            }

            if (is_array($debugInfo)) {
                //Pretty view
                $debugInfo = (object)$debugInfo;
            }

            if (is_object($debugInfo)) {
                //We are not including syntax elements here
                return $this->renderObject($r, $debugInfo, $level, false, get_class($value));
            }

            return $header
                . $this->renderValue($r, $debugInfo, '', $level + (is_scalar($value)), true)
                . $r->indent($level) . $r->apply(')', 'syntax', ')') . "\n";
        }

        $refection = new \ReflectionObject($value);

        $output = '';
        foreach ($refection->getProperties() as $property) {
            $output .= $this->renderProperty($r, $value, $property, $level);
        }

        //Header, content, footer
        return $header . $output . $r->indent($level) . $r->apply(')', 'syntax', ')') . "\n";
    }

    /**
     * @param RendererInterface   $r
     * @param object              $value
     * @param \ReflectionProperty $p
     * @param int                 $level
     *
     * @return string
     */
    private function renderProperty(RendererInterface $r, $value, \ReflectionProperty $p, int $level): string
    {
        if ($p->isStatic()) {
            return '';
        }

        if (
            !($value instanceof \stdClass)
            && is_string($p->getDocComment())
            && strpos($p->getDocComment(), '@internal') !== false
        ) {
            // Memory loop while reading doc comment for stdClass variables?
            // Report a PHP bug about treating comment INSIDE property declaration as doc comment.
            return '';
        }

        //Property access level
        $access = $this->getAccess($p);

        //To read private and protected properties
        $p->setAccessible(true);

        if ($value instanceof \stdClass) {
            $name = $r->apply($p->getName(), 'dynamic');
        } else {
            //Property name includes access level
            $name = $p->getName() . $r->apply(':' . $access, 'access', $access);
        }

        return $this->renderValue($r, $p->getValue($value), $name, $level + 1);
    }

    /**
     * Fetch information about the closure.
     *
     * @param \Closure $closure
     * @return array
     */
    private function describeClosure(\Closure $closure): array
    {
        try {
            $r = new \ReflectionFunction($closure);
        } catch (\ReflectionException $e) {
            return ['closure' => 'unable to resolve'];
        }

        return [
            'name' => $r->getName() . " (lines {$r->getStartLine()}:{$r->getEndLine()})",
            'file' => $r->getFileName(),
            'this' => $r->getClosureThis()
        ];
    }

    /**
     * Property access level label.
     *
     * @param \ReflectionProperty $p
     *
     * @return string
     */
    private function getAccess(\ReflectionProperty $p): string
    {
        if ($p->isPrivate()) {
            return 'private';
        } elseif ($p->isProtected()) {
            return 'protected';
        }

        return 'public';
    }
}
