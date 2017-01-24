<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Environment;

use Dotenv\Loader;

/**
 * Lower level access to env variables. Built at top of Dotenv package.
 */
class Parser extends Loader
{
    /**
     * Parse environment file and return it's values.
     *
     * @return array
     */
    public function parse(): array
    {
        $values = [];

        $lines = $this->readLinesFromFile($this->filePath);
        foreach ($lines as $line) {
            if ($this->isComment($line)) {
                continue;
            }

            if ($this->looksLikeSetter($line)) {
                list($name, $value) = $this->normaliseEnvironmentVariable($line, null);
                $values[$name] = $value;
            }
        }

        return $values;
    }
}