<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

use Spiral\Debug\Dumper;
use Spiral\Debug\RendererInterface;

/**
 * Nicely colorize argument and argument values in stack trace.
 *
 * @internal
 */
class ValueWrapper
{
    /** @var RendererInterface */
    private $r;

    /** @var Dumper */
    private $dumper;

    /** @var int */
    private $verbosity;

    /**
     * Aggregated list of value dumps, only for DEBUG mode.
     *
     * @var array
     */
    private $values = [];

    public function __construct(Dumper $dumper, RendererInterface $renderer, int $verbosity)
    {
        $this->dumper = $dumper;
        $this->r = $renderer;
        $this->verbosity = $verbosity;
    }

    public function wrap(array $args): array
    {
        $result = [];
        foreach ($args as $arg) {
            $display = $type = strtolower(gettype($arg));

            if (is_numeric($arg)) {
                $result[] = $this->r->apply($arg, 'value', $type);
                continue;
            } elseif (is_bool($arg)) {
                $result[] = $this->r->apply($arg ? 'true' : 'false', 'value', $type);
                continue;
            } elseif (is_null($arg)) {
                $result[] = $this->r->apply('null', 'value', $type);
                continue;
            }

            if (is_object($arg)) {
                $reflection = new \ReflectionClass($arg);
                $display = sprintf('<span title="%s">%s</span>', $reflection->getName(), $reflection->getShortName());
            }

            $type = $this->r->apply($display, 'value', $type);

            if ($this->verbosity < HandlerInterface::VERBOSITY_DEBUG) {
                $result[] = sprintf('<span>%s</span>', $type);
            } else {
                $hash = is_object($arg) ? spl_object_hash($arg) : md5(json_encode($arg));

                if (!isset($this->values[$hash])) {
                    $this->values[$hash] = $this->dumper->dump($arg, Dumper::RETURN);
                }

                $result[] = sprintf('<span onclick="_da(\'%s\')">%s</span>', $hash, $type);
            }
        }

        return $result;
    }

    /**
     * Get all aggregated values for later rendering on a page.
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
