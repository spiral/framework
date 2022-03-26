<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Style;

use Codedungeon\PHPCliColors\Color;
use Spiral\Exceptions\StyleInterface;

/**
 * Colorful source code highlighter for CLI applications.
 */
class ConsoleStyle implements StyleInterface
{
    protected array $templates = [
        'token'  => '%s%s' . Color::RESET,
        'line'   => Color::LIGHT_CYAN . ' %s ' . Color::RESET . " %s\n",
        'active' => Color::BG_RED . ' ' . Color::LIGHT_WHITE . '%s ' . Color::RESET . " %s\n",
    ];

    protected array $style = [
        Color::YELLOW        => [
            T_STATIC,
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_CLASS,
            T_NEW,
            T_FINAL,
            T_ABSTRACT,
            T_IMPLEMENTS,
            T_CONST,
            T_ECHO,
            T_CASE,
            T_FUNCTION,
            T_GOTO,
            T_INCLUDE,
            T_REQUIRE,
            T_REQUIRE_ONCE,
            T_VAR,
            T_INSTANCEOF,
            T_INTERFACE,
            T_THROW,
            T_ARRAY,
            T_IF,
            T_ELSE,
            T_ELSEIF,
            T_TRY,
            T_CATCH,
            T_CLONE,
            T_WHILE,
            T_FOR,
            T_DO,
            T_UNSET,
            T_FOREACH,
            T_RETURN,
            T_EXIT,
            T_USE,
            T_OPEN_TAG,
            T_CLOSE_TAG,
            T_OPEN_TAG_WITH_ECHO,
            ';',
            ',',
        ],
        Color::LIGHT_BLUE    => [
            T_DNUMBER,
            T_LNUMBER,
        ],
        Color::GREEN         => [
            T_CONSTANT_ENCAPSED_STRING,
            T_ENCAPSED_AND_WHITESPACE,
        ],
        Color::GRAY          => [
            T_COMMENT,
            T_DOC_COMMENT,
        ],
        Color::LIGHT_MAGENTA => [
            T_VARIABLE,
        ],
        Color::LIGHT_YELLOW  => [
            '->' . T_STRING,
            '::' . T_STRING,
        ],
    ];

    public function token(array $token, array $previous): string
    {
        $style = $this->getStyle($token, $previous);

        if (!\str_contains((string) $token[1], "\n")) {
            return \sprintf($this->templates['token'], $style, $token[1]);
        }

        $lines = [];
        foreach (\explode("\n", (string) $token[1]) as $line) {
            $lines[] = \sprintf($this->templates['token'], $style, $line);
        }

        return \implode("\n", $lines);
    }

    public function line(int $number, string $code, bool $target = false): string
    {
        return \sprintf(
            $this->templates[$target ? 'active' : 'line'],
            \str_pad((string)$number, 4, ' ', STR_PAD_LEFT),
            $code
        );
    }

    /**
     * Get styles for a given token.
     */
    private function getStyle(array $token, array $previous): string
    {
        if (!empty($previous)) {
            foreach ($this->style as $style => $tokens) {
                if (\in_array($previous[1] . $token[0], $tokens)) {
                    return $style;
                }
            }
        }

        foreach ($this->style as $style => $tokens) {
            if (\in_array($token[0], $tokens)) {
                return $style;
            }
        }

        return Color::WHITE;
    }
}
