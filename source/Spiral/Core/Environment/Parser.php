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
    public function parse()
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

    protected function parseLine($name, $value = null)
    {
        list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);

        // Don't overwrite existing environment variables if we're immutable
        // Ruby's dotenv does this with `ENV[key] ||= value`.
        if ($this->immutable === true && !is_null($this->getEnvironmentVariable($name))) {
            return;
        }

        return [$name, $value];
    }
}