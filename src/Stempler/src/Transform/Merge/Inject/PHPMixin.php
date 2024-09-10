<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge\Inject;

/**
 * PHPMixin provides the ability to safely inject nodes into PHP source code using given macro function.
 */
final class PHPMixin
{
    private array $blocks = [];

    public function __construct(
        private readonly array $tokens,
        string $func
    ) {
        $this->parse($func);
    }

    public function compile(): string
    {
        $replace = [];

        foreach ($this->blocks as $block) {
            for ($i = $block['start']; $i <= $block['end']; $i++) {
                $replace[$i] = '';
            }

            $replace[$block['start']] = $block['value'];
        }

        $result = '';
        foreach ($this->tokens as $position => $token) {
            if (\array_key_exists($position, $replace)) {
                $result .= $replace[$position];
                continue;
            }

            if (\is_string($token)) {
                $result .= $token;
                continue;
            }

            $result .= $token[1];
        }

        return $result;
    }

    /**
     * Compiles the PHP blocks (with replacements) but excludes the php open, close tag and echo function.
     */
    public function trimBody(): string
    {
        $replace = [];

        foreach ($this->blocks as $block) {
            for ($i = $block['start']; $i <= $block['end']; ++$i) {
                $replace[$i] = '';
            }

            $replace[$block['start']] = $block['value'];
        }

        $result = '';
        foreach ($this->tokens as $position => $token) {
            if (\array_key_exists($position, $replace)) {
                $result .= $replace[$position];
                continue;
            }

            if (\is_string($token)) {
                $result .= $token;
                continue;
            }

            if (\in_array($token[0], [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG, T_ECHO])) {
                continue;
            }

            $result .= $token[1];
        }

        return \rtrim(\trim($result), ';');
    }

    /**
     * Get macros detected in PHP code and their default values (if any).
     */
    public function getBlocks(): array
    {
        $result = [];
        foreach ($this->blocks as $name => $macro) {
            $result[$name] = $macro['value'];
        }

        return $result;
    }

    public function has(string $block): bool
    {
        return isset($this->blocks[$block]);
    }

    public function set(string $block, string $value): void
    {
        if (!isset($this->blocks[$block])) {
            return;
        }

        $this->blocks[$block]['value'] = $value;
    }

    private function parse(string $func): void
    {
        $level = 0;
        $start = $name = $value = null;
        foreach ($this->tokens as $position => $token) {
            if (!\is_array($token)) {
                $token = [$token, $token, 0];
            }

            switch ($token[0]) {
                case '(':
                    if ($start !== null) {
                        $level++;
                        $value .= $token[1];
                    }
                    break;
                case ')':
                    if ($start === null) {
                        break;
                    }

                    $level--;
                    $value .= $token[1];
                    if ($level === 0) {
                        $this->blocks[$name] = [
                            'start' => $start,
                            'value' => trim($value),
                            'end'   => $position,
                        ];

                        // reset
                        $start = $name = $value = null;
                    }
                    break;
                case T_STRING:
                    if ($token[1] === $func) {
                        $start = $position;
                        $value = $token[1];
                        break;
                    }

                    if ($start !== null) {
                        $value .= $token[1];
                    }
                    break;
                case T_CONSTANT_ENCAPSED_STRING:
                    if ($start === null) {
                        break;
                    }

                    if ($name === null) {
                        $name = \stripcslashes(\substr((string) $token[1], 1, -1));
                    }
                    $value .= $token[1];
                    break;
                case ',':
                    $value .= $token[1];
                    break;
                default:
                    if ($start !== null) {
                        $value .= $token[1];
                    }
            }
        }
    }
}
