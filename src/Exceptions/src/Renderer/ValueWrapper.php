<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Renderer;

use Spiral\Debug\Dumper;
use Spiral\Debug\RendererInterface;
use Spiral\Exceptions\Verbosity;

/**
 * Nicely colorize argument and argument values in stack trace.
 *
 * @internal
 */
class ValueWrapper
{
    /**
     * Aggregated list of value dumps, only for DEBUG mode.
     */
    private array $values = [];

    public function __construct(
        private Dumper $dumper,
        private RendererInterface $renderer,
        private Verbosity $verbosity
    ) {
    }

    public function wrap(array $args): array
    {
        $result = [];
        foreach ($args as $arg) {
            $display = \strtolower(\gettype($arg));
            $type = $display;

            if (\is_numeric($arg)) {
                $result[] = $this->renderer->apply($arg, 'value', $type);
                continue;
            }

            if (\is_bool($arg)) {
                $result[] = $this->renderer->apply($arg ? 'true' : 'false', 'value', $type);
                continue;
            }

            if (\is_null($arg)) {
                $result[] = $this->renderer->apply('null', 'value', $type);
                continue;
            }

            if (\is_object($arg)) {
                $reflection = new \ReflectionClass($arg);
                $display = \sprintf('<span title="%s">%s</span>', $reflection->getName(), $reflection->getShortName());
            }

            $type = $this->renderer->apply($display, 'value', $type);

            if ($this->verbosity->value < Verbosity::DEBUG->value) {
                $result[] = \sprintf('<span>%s</span>', $type);
            } else {
                $hash = \is_object($arg) ? \spl_object_hash($arg) : \md5(\json_encode($arg));

                if (!isset($this->values[$hash])) {
                    $this->values[$hash] = $this->dumper->dump($arg, Dumper::RETURN);
                }

                $result[] = \sprintf('<span onclick="_da(\'%s\')">%s</span>', $hash, $type);
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
