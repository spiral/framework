<?php

declare(strict_types=1);

namespace Spiral\Prototype\Annotation;

/**
 * Simple annotation parser and compiler.
 *
 * @deprecated since v3.9.0
 */
final class Parser
{
    /** @var array<int, Line> */
    public array $lines = [];

    public function __construct(string $comment)
    {
        $lines = \explode("\n", $comment);

        foreach ($lines as $line) {
            // strip up comment prefix
            /** @var string $line */
            $line = \preg_replace('/[\t ]*[\/]?\*[\/]? ?/', '', $line);

            if (\preg_match('/ *@([^ ]+) (.*)/u', $line, $matches)) {
                $this->lines[] = new Line($matches[2], $matches[1]);
            } else {
                $this->lines[] = new Line($line);
            }
        }

        if (isset($this->lines[0]) && $this->lines[0]->isEmpty()) {
            \array_shift($this->lines);
        }

        if (isset($this->lines[\count($this->lines) - 1]) && $this->lines[\count($this->lines) - 1]->isEmpty()) {
            \array_pop($this->lines);
        }
    }

    public function compile(): string
    {
        $result = [];
        $result[] = '/**';

        // skip first and last tokens
        foreach ($this->lines as $line) {
            if ($line->type === null) {
                $result[] = \sprintf(' * %s', $line->value);
                continue;
            }

            $result[] = \sprintf(' * @%s %s', $line->type, $line->value);
        }

        $result[] = ' */';

        return \implode("\n", $result);
    }
}
