<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

/**
 * Highlights portion of PHP file using given Style.
 */
class Highlighter
{
    /** @var StyleInterface */
    private $r;

    public function __construct(StyleInterface $renderer)
    {
        $this->r = $renderer;
    }

    /**
     * Highlight PHP source and return N lines around target line.
     */
    public function highlightLines(string $source, int $line, int $around = 5): string
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $this->highlight($source)));

        $result = '';
        foreach ($lines as $number => $code) {
            $human = $number + 1;
            if (!empty($around) && ($human < $line - $around || $human >= $line + $around + 1)) {
                //Not included in a range
                continue;
            }

            $result .= $this->r->line($human, mb_convert_encoding($code, 'utf-8'), $human === $line);
        }

        return $result;
    }

    /**
     * Returns highlighted PHP source.
     */
    public function highlight(string $source): string
    {
        $result = '';
        $previous = [];
        foreach ($this->getTokens($source) as $token) {
            $result .= $this->r->token($token, $previous);
            $previous = $token;
        }

        return $result;
    }

    /**
     * Get all tokens from PHP source normalized to always include line number.
     */
    private function getTokens(string $source): array
    {
        $tokens = [];
        $line = 0;

        foreach (token_get_all($source) as $token) {
            if (isset($token[2])) {
                $line = $token[2];
            }

            if (!is_array($token)) {
                $token = [$token, $token, $line];
            }

            $tokens[] = $token;
        }

        return $tokens;
    }
}
