<?php

declare(strict_types=1);

namespace Spiral\Boot;

/**
 * @internal Use {@see \Spiral\Boot\EnvironmentInterface} instead
 */
final class Environment implements EnvironmentInterface
{
    private const VALUE_MAP = [
        'true'    => true,
        '(true)'  => true,
        'false'   => false,
        '(false)' => false,
        'null'    => null,
        '(null)'  => null,
        'empty'   => '',
    ];

    private ?string $id = null;
    private array $values;

    public function __construct(
        array $values = [],
        private readonly bool $overwrite = false
    ) {
        $this->values = $values + $_ENV + $_SERVER;
    }

    public function getID(): string
    {
        if (empty($this->id)) {
            $this->id = \md5(\serialize($this->values));
        }

        return $this->id;
    }

    public function set(string $name, mixed $value): self
    {
        if (\array_key_exists($name, $this->values) && !$this->overwrite) {
            return $this;
        }

        $this->values[$name] = $_ENV[$name] = $value;
        \putenv("$name=$value");

        $this->id = null;

        return $this;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (isset($this->values[$name])) {
            return $this->normalize($this->values[$name]);
        }

        return $default;
    }

    /**
     * Get all environment values.
     */
    public function getAll(): array
    {
        $result = [];

        foreach ($this->values as $key => $value) {
            $result[$key] = $this->normalize($value);
        }

        return $result;
    }

    protected function normalize(mixed $value): mixed
    {
        if (!\is_string($value)) {
            return $value;
        }

        $alias = \strtolower($value);
        if (isset(self::VALUE_MAP[$alias])) {
            return self::VALUE_MAP[$alias];
        }

        return $value;
    }
}
