<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Core;

/**
 * Environent with ability to track and set _ENV values.
 */
class Environment implements EnvironmentInterface
{
    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var array
     */
    private $values = [];

    /**
     * @throws \Spiral\Core\Exceptions\EnvironmentException
     */
    public function __construct()
    {
        $this->load();
    }

    /**
     * {@inheritdoc}
     */
    public function getID(): string
    {
        if (empty($this->id)) {
            $this->id = md5(serialize($this->values));
        }

        return $this->id;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function set(string $name, $value): DotenvEnvironment
    {
        $this->values[$name] = $_ENV[$name] = $value;
        putenv("$name=$value");

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, $default = null)
    {
        if (array_key_exists($name, $this->values)) {
            return $this->normalize($this->values[$name]);
        }

        return $default;
    }

    /**
     * Load environment values from _ENV.
     */
    protected function load()
    {
        $this->values = $_ENV;
    }

    /**
     * Normalize env value.
     *
     * @param mixed $value
     *
     * @return bool|null|string
     */
    protected function normalize($value)
    {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'null':
            case '(null)':
                return null;

            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}