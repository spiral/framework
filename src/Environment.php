<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

final class Environment implements EnvironmentInterface
{
    /** @var string|null */
    private $id = null;

    /** @var array */
    private $values = [];

    /**
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values + $_ENV + $_SERVER;
    }

    /**
     * @inheritdoc
     */
    public function getID(): string
    {
        if (empty($this->id)) {
            $this->id = md5(serialize($this->values));
        }

        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function set(string $name, string $value)
    {
        $this->values[$name] = $_ENV[$name] = $value;
        putenv("$name=$value");

        $this->id = null;
    }

    /**
     * @inheritdoc
     */
    public function get(string $name, string $default = null): ?string
    {
        if (isset($this->values[$name])) {
            return $this->normalize($this->values[$name]);
        }

        return $default;
    }

    /**
     * @param mixed $value
     * @return bool|null|string
     */
    protected function normalize($value)
    {
        switch (trim(strtolower($value), '()')) {
            case 'true':
                return true;

            case 'false':
                return false;

            case 'null':
                return null;

            case 'empty':
                return '';

            default:
                return $value;
        }
    }
}