<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions\Style;

use Spiral\Exceptions\StyleInterface;

/**
 * HTML based styling of given source code. Attention, you have to manually wrap generated code
 * using html block.
 */
class HtmlStyle implements StyleInterface
{
    /**
     * Default code styles.
     */
    public const DEFAULT = [
        'color: blue; font-weight: bold;'   => [
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
            T_INCLUDE_ONCE,
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
        ],
        'font-weight: bold;'                => [
            ';',
            ',',
        ],
        'color: blue'                       => [
            T_DNUMBER,
            T_LNUMBER,
        ],
        'color: black; font: weight: bold;' => [
            T_OPEN_TAG,
            T_CLOSE_TAG,
            T_OPEN_TAG_WITH_ECHO,
        ],
        'color: gray;'                      => [
            T_COMMENT,
            T_DOC_COMMENT,
        ],
        'color: green; font-weight: bold;'  => [
            T_CONSTANT_ENCAPSED_STRING,
            T_ENCAPSED_AND_WHITESPACE,
        ],
        'color: #660000;'                   => [
            T_VARIABLE,
        ],
    ];

    public const INVERTED = [
        'color: #FF8B00; font-weight: bold;' => [
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
            T_INCLUDE_ONCE,
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
            T_EXTENDS,
            ';',
            ',',
        ],
        'color: black; font: weight: bold;'  => [
            T_OPEN_TAG,
            T_CLOSE_TAG,
            T_OPEN_TAG_WITH_ECHO,
        ],
        'color: #9C9C9C;'                    => [
            T_COMMENT,
            T_DOC_COMMENT,
        ],
        'color: #A5C261;'                    => [
            T_CONSTANT_ENCAPSED_STRING,
            T_ENCAPSED_AND_WHITESPACE,
            T_DNUMBER,
            T_LNUMBER,
        ],
        'color: #D0D0FF;'                    => [
            T_VARIABLE,
        ],
        'color: #E6D100;'                    => [
            '->' . T_STRING,
            '::' . T_STRING,
        ],
    ];

    /**
     * Style templates.
     *
     * @var array
     */
    protected $templates = [
        'token'  => '<span style="%s">%s</span>',
        'line'   => "<div><span class=\"number\">%d</span>%s</div>\n",
        'active' => "<div class=\"active\"><span class=\"number\">%d</span>%s</div>\n",
    ];

    /**
     * Style associated with token types.
     *
     * @var array
     */
    protected $style = self::DEFAULT;

    /**
     * @param array $style
     */
    public function __construct(array $style = self::DEFAULT)
    {
        $this->style = $style;
    }

    /**
     * @inheritdoc
     */
    public function token(array $token, array $previous): string
    {
        $style = $this->getStyle($token, $previous);

        if (strpos($token[1], "\n") === false) {
            return sprintf($this->templates['token'], $style, htmlspecialchars($token[1]));
        }

        $lines = [];
        foreach (explode("\n", $token[1]) as $line) {
            $lines[] = sprintf($this->templates['token'], $style, htmlspecialchars($line));
        }

        return implode("\n", $lines);
    }

    /**
     * @inheritdoc
     */
    public function line(int $number, string $code, bool $target = false): string
    {
        return sprintf($this->templates[$target ? 'active' : 'line'], $number, $code);
    }

    /**
     * Get styles for a given token.
     *
     * @param array $token
     * @param array $previous
     * @return string
     */
    private function getStyle(array $token, array $previous): string
    {
        if (!empty($previous)) {
            foreach ($this->style as $style => $tokens) {
                if (in_array($previous[1] . $token[0], $tokens)) {
                    return $style;
                }
            }
        }

        foreach ($this->style as $style => $tokens) {
            if (in_array($token[0], $tokens)) {
                return $style;
            }
        }

        return '';
    }
}
