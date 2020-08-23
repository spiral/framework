<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Reactor\Partial;

use Spiral\Reactor\ReplaceableInterface;

/**
 * Wraps docBlock comment (by representing it as string lines).
 */
class Comment extends Source implements ReplaceableInterface
{
    /**
     * {@inheritdoc}
     * @return self
     */
    public function replace($search, $replace): Comment
    {
        $lines = $this->getLines();

        array_walk($lines, static function (&$line) use ($search, $replace): void {
            $line = str_replace($search, $replace, $line);
        });

        return $this->setLines($lines);
    }

    /**
     * {@inheritdoc}
     */
    public function render(int $indentLevel = 0): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $result = $this->addIndent("/**\n", $indentLevel);
        foreach ($this->getLines() as $line) {
            $result .= $this->addIndent(" * {$line}\n", $indentLevel);
        }

        $result .= $this->addIndent(' */', $indentLevel);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareLine(string $line): ?string
    {
        $line = trim($line);
        if (in_array($line, ['/*', '/**', '*/'], true)) {
            return null;
        }

        return parent::prepareLine(preg_replace('/^(\s)*(\*)+\s?/', '', $line));
    }
}
