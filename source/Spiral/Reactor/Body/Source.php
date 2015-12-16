<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Body;

use Spiral\Reactor\Exceptions\MultilineException;
use Spiral\Reactor\Prototypes\Declaration;
use Spiral\Support\Strings;

/**
 * Represents set of lines (function source, docComment).
 */
class Source extends Declaration
{
    /**
     * @var array
     */
    private $lines = [];

    /**
     * @param array $lines
     */
    public function __construct(array $lines = [])
    {
        $this->lines = $lines;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->lines);
    }

    /**
     * @param array $lines
     * @return $this
     */
    public function setLines(array $lines)
    {
        $this->lines = $lines;

        return $this;
    }

    /**
     * @param array $lines
     * @return $this
     */
    public function addLines(array $lines)
    {
        $this->lines = array_merge($this->lines, $lines);

        return $this;
    }

    /**
     * @param string $line
     * @return $this
     * @throws MultilineException
     */
    public function addLine($line)
    {
        if (strpos($line, "\n") !== false) {
            throw new MultilineException(
                "New line character is forbidden in addLine method argument."
            );
        }

        $this->lines[] = $line;

        return $this;
    }

    /**
     * @param string $string
     * @param bool   $cutIndents Function Strings::normalizeIndents will be applied.
     * @return $this
     */
    public function setString($string, $cutIndents = false)
    {
        return $this->setLines($this->fetchLines($string, $cutIndents));
    }

    /**
     * @param string $string
     * @param bool   $cutIndents Function Strings::normalizeIndents will be applied.
     * @return $this
     */
    public function addString($string, $cutIndents = false)
    {
        return $this->addLines($this->fetchLines($string, $cutIndents));
    }

    /**
     * @return array
     */
    public function getLines()
    {
        return $this->lines;
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        $lines = $this->lines;
        array_walk($lines, function (&$line) use ($indentLevel) {
            $line = $this->indent($line, $indentLevel);
        });

        return join("\n", $lines);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render(0);
    }

    /**
     * Converts input string into set of lines.
     *
     * @param string $string
     * @param bool   $cutIndents
     * @return array
     */
    public function fetchLines($string, $cutIndents)
    {
        if ($cutIndents) {
            $string = Strings::normalizeEndings($string, false);
        }

        $lines = explode("\n", Strings::normalizeEndings($string, false));

        //Pre-processing
        return array_map([$this, 'prepareLine'], $lines);
    }

    /**
     * @param string $string
     * @param bool   $cutIndents Function Strings::normalizeIndents will be applied.
     * @return Source
     */
    public static function fromString($string, $cutIndents = false)
    {
        $source = new self();

        return $source->setString($string, $cutIndents);
    }

    /**
     * Applied to every string before adding it to lines.
     *
     * @param string $line
     * @return string
     */
    protected function prepareLine($line)
    {
        return $line;
    }
}