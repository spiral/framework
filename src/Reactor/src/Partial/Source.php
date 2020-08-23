<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Spiral\Reactor\AbstractDeclaration;
use Spiral\Reactor\Exception\MultilineException;

/**
 * Represents set of lines (function source, docComment).
 */
class Source extends AbstractDeclaration
{
    /**
     * @var array
     */
    private $lines;

    /**
     * @param array $lines
     */
    public function __construct(array $lines = [])
    {
        $this->lines = $lines;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render(0);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->lines);
    }

    /**
     * @param array $lines
     * @return self|$this
     */
    public function setLines(array $lines): Source
    {
        $this->lines = $lines;

        return $this;
    }

    /**
     * @param string $line
     * @return self
     * @throws MultilineException
     */
    public function addLine(string $line): Source
    {
        if (strpos($line, "\n") !== false) {
            throw new MultilineException(
                'New line character is forbidden in addLine method argument'
            );
        }

        $this->lines[] = $line;

        return $this;
    }

    /**
     * @param string $string
     * @param bool   $cutIndents Function Strings::normalizeIndents will be applied.
     * @return self
     */
    public function setString(string $string, bool $cutIndents = false): Source
    {
        return $this->setLines($this->fetchLines($string, $cutIndents));
    }

    /**
     * @return array
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * {@inheritdoc}
     */
    public function render(int $indentLevel = 0): string
    {
        $lines = $this->lines;
        array_walk($lines, function (&$line) use ($indentLevel): void {
            $line = $this->addIndent($line, $indentLevel);
        });

        return implode("\n", $lines);
    }

    /**
     * Create version of source cut from specific string location.
     *
     * @param string $string
     * @param bool   $cutIndents Function Strings::normalizeIndents will be applied.
     * @return Source
     */
    public static function fromString(string $string, bool $cutIndents = false): Source
    {
        $source = new self();

        return $source->setString($string, $cutIndents);
    }

    /**
     * Normalize string endings to avoid EOL problem. Replace \n\r and multiply new lines with
     * single \n.
     *
     * @param string $string String to be normalized.
     * @param bool   $joinMultiple Join multiple new lines into one.
     * @return string
     */
    public static function normalizeEndings(string $string, bool $joinMultiple = true): string
    {
        if (!$joinMultiple) {
            return str_replace("\r\n", "\n", $string);
        }

        return preg_replace('/[\n\r]+/', "\n", $string);
    }

    /**
     * Shift all string lines to have minimum indent size set to 0.
     *
     * Example:
     * |-a
     * |--b
     * |--c
     * |---d
     *
     * Output:
     * |a
     * |-b
     * |-c
     * |--d
     *
     * @param string $string Input string with multiple lines.
     * @param string $tabulationCost How to treat \t symbols relatively to spaces. By default, this
     *                               is set to 4 spaces.
     * @return string
     */
    public static function normalizeIndents(string $string, string $tabulationCost = '   '): string
    {
        $string = self::normalizeEndings($string, false);
        $lines = explode("\n", $string);
        $minIndent = null;
        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }
            $line = str_replace("\t", $tabulationCost, $line);
            //Getting indent size
            if (!preg_match('/^( +)/', $line, $matches)) {
                //Some line has no indent
                return $string;
            }
            if ($minIndent === null) {
                $minIndent = strlen($matches[1]);
            }
            $minIndent = min($minIndent, strlen($matches[1]));
        }
        //Fixing indent
        foreach ($lines as &$line) {
            if (empty($line)) {
                continue;
            }
            //Getting line indent
            preg_match("/^([ \t]+)/", $line, $matches);
            $indent = $matches[1];
            if (!trim($line)) {
                $line = '';
                continue;
            }
            //Getting new indent
            $useIndent = str_repeat(
                ' ',
                strlen(str_replace("\t", $tabulationCost, $indent)) - $minIndent
            );
            $line = $useIndent . substr($line, strlen($indent));
            unset($line);
        }

        return implode("\n", $lines);
    }

    /**
     * Converts input string into set of lines.
     *
     * @param string $string
     * @param bool   $cutIndents
     * @return array
     */
    protected function fetchLines(string $string, bool $cutIndents): array
    {
        if ($cutIndents) {
            $string = self::normalizeIndents($string, '');
        }

        $lines = explode("\n", self::normalizeEndings($string, false));

        //Pre-processing
        return array_filter(array_map([$this, 'prepareLine'], $lines), static function ($line): bool {
            return $line !== null;
        });
    }

    /**
     * Applied to every string before adding it to lines.
     *
     * @param string $line
     * @return string
     */
    protected function prepareLine(string $line): ?string
    {
        return $line;
    }
}
