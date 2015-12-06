<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Body;

use Spiral\Reactor\ReplaceableInterface;

/**
 * Wraps docBlock comment (by representing it as string lines).
 */
class DocComment extends Source implements ReplaceableInterface
{
    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function replace($search, $replace)
    {
        $lines = $this->getLines();

        array_walk($lines, function (&$line) use ($search, $replace) {
            $line = str_replace($search, $replace, $line);
        });

        return $this->setLines($lines);
    }

    /**
     * {@inheritdoc}
     */
    public function render($indentLevel = 0)
    {
        if ($this->isEmpty()) {
            return '';
        }

        $result = $this->indent("/**\n", $indentLevel);
        foreach ($this->getLines() as $line) {
            $result .= $this->indent(" * {$line}\n", $indentLevel);
        }

        $result .= $this->indent(" */", $indentLevel);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareLine($line)
    {
        $line = trim($line);

        if ($line === '/*' || $line === '/**' || $line === '*/') {
            return '';
        }

        return parent::prepareLine(preg_replace('/^(\s)*(\*)+/si', ' ', $line));
    }
}