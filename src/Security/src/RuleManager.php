<?php

declare(strict_types=1);

namespace Spiral\Security;

use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Singleton;
use Spiral\Security\Exception\RuleException;
use Spiral\Security\Rule\CallableRule;

/**
 * Provides ability to request permissions rules based on it's name. Rules are being fetched from container.
 */
#[Singleton]
final class RuleManager implements RulesInterface
{
    private array $rules = [];

    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function set(string $name, mixed $rule = null): RuleManager
    {
        if (empty($rule)) {
            $rule = $name;
        }

        if (!$this->validateRule($rule)) {
            throw new RuleException("Unable to set rule '{$name}', invalid rule body");
        }

        $this->rules[$name] = $rule;

        return $this;
    }

    public function remove(string $name): RuleManager
    {
        if (!$this->has($name)) {
            throw new RuleException("Undefined rule '{$name}'");
        }

        unset($this->rules[$name]);

        return $this;
    }

    public function has(string $name): bool
    {
        return match (true) {
            isset($this->rules[$name]) => true,
            // We are allowing to use class names without direct registration
            \class_exists($name) => true,
            // Relying on container binding
            default => $this->container->has($name)
        };
    }

    public function get(string $name): RuleInterface
    {
        if (!$this->has($name)) {
            throw new RuleException(\sprintf("Undefined rule '%s'", $name));
        }

        if (!isset($this->rules[$name])) {
            //Rule represented as class name
            $rule = $name;
        } else {
            $rule = $this->rules[$name];
        }

        if ($rule instanceof RuleInterface) {
            return $rule;
        }

        if (\is_string($rule)) {
            //We are expecting that rule points to
            $rule = $this->container->get($rule);

            if (!$rule instanceof RuleInterface) {
                throw new RuleException(\sprintf(
                    "Rule '%s' must point to RuleInterface, '%s' given",
                    $name,
                    !empty($rule) ? $rule::class : 'null'
                ));
            }

            return $rule;
        }

        //We have to respond using RuleInterface (expecting that rule is callable)
        return new CallableRule($rule);
    }

    /**
     * Must return true if rule is valid.
     */
    private function validateRule(mixed $rule): bool
    {
        if ($rule instanceof \Closure || $rule instanceof RuleInterface) {
            return true;
        }

        if (\is_array($rule)) {
            return \is_callable($rule, true);
        }

        if (\is_string($rule) && \class_exists($rule)) {
            try {
                $reflection = new \ReflectionClass($rule);
            } catch (\ReflectionException) {
                return false;
            }

            return $reflection->isSubclassOf(RuleInterface::class);
        }

        return false;
    }
}
