<?php

declare(strict_types=1);

namespace Spiral\Validation\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\Core\Traits\Config\AliasTrait;
use Spiral\Validation\Exception\ValidationException;

final class ValidatorConfig extends InjectableConfig
{
    use AliasTrait;

    public const CONFIG = 'validation';

    /**
     * @var array
     */
    protected $config = [
        'conditions' => [],
        'checkers'   => [],
        'aliases'    => [],
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!empty($this->config['aliases'])) {
            $this->config['aliases'] = $this->normalizeAliases($this->config['aliases']);
        }
    }

    public function hasChecker(string $name): bool
    {
        return isset($this->config['checkers'][$name]);
    }

    /**
     *
     * @throws ValidationException
     */
    public function getChecker(string $name): Autowire
    {
        if (!$this->hasChecker($name)) {
            throw new ValidationException(\sprintf('Undefined checker `%s``.', $name));
        }

        $instance = $this->wire('checkers', $name);
        if ($instance !== null) {
            return $instance;
        }

        throw new ValidationException(\sprintf('Invalid checker definition for `%s`.', $name));
    }

    public function hasCondition(string $name): bool
    {
        return isset($this->config['conditions'][$name]);
    }

    public function getCondition(string $name): Autowire
    {
        if (!$this->hasCondition($name)) {
            throw new ValidationException(\sprintf('Undefined condition `%s`.', $name));
        }

        $instance = $this->wire('conditions', $name);
        if ($instance !== null) {
            return $instance;
        }

        throw new ValidationException(\sprintf('Invalid condition definition for `%s`.', $name));
    }

    /**
     * Return validation function or checker after applying all alias redirects.
     */
    public function mapFunction(array|string|\Closure $function): array|string|\Closure
    {
        if (\is_string($function)) {
            $function = $this->resolveAlias($function);
            if (\str_contains($function, ':')) {
                $function = \explode(':', $function);
            }
        }

        return $function;
    }

    private function wire(string $section, string $name): ?Autowire
    {
        if (\is_string($this->config[$section][$name])) {
            return new Autowire($this->config[$section][$name]);
        }

        if (isset($this->config[$section][$name]['class'])) {
            return new Autowire(
                $this->config[$section][$name]['class'],
                $this->config[$section][$name]['options'] ?? []
            );
        }

        return null;
    }

    /**
     * Normalize all defined aliases.
     *
     * @return array<string>
     */
    private function normalizeAliases(array $aliases): array
    {
        return \array_map(static fn ($value) => \str_replace('::', ':', $value), $aliases);
    }
}
