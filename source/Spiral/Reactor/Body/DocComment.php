<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor;

use Spiral\Reactor\Body\Source;

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
    protected function prepareLine($line)
    {
        $line = trim($line);

        if ($line === '/*' || $line === '/**' || $line === '*/') {
            return '';
        }

        return parent::prepareLine(preg_replace('/^(\s)*(\*)+/si', ' ', $line));
    }
}