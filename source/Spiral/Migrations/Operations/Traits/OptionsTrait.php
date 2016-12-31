<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Migrations\Operations\Traits;

/**
 * Consumer must define property "aliases".
 */
trait OptionsTrait
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function hasOption(string $name): bool
    {
        if (array_key_exists($name, $this->options)) {
            return true;
        }

        if (!isset($this->aliases) || !isset($this->aliases[$name])) {
            return false;
        }

        foreach ($this->aliases[$name] as $name) {
            return $this->hasOption($name);
        }

        return false;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getOption(string $name, $default = null)
    {
        if (!$this->hasOption($name)) {
            return $default;
        }

        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        if (!isset($this->aliases) || !isset($this->aliases[$name])) {
            return false;
        }

        foreach ($this->aliases[$name] as $name) {
            return $this->hasOption($name);
        }

        return false;
    }
}