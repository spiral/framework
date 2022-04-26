<?php

declare(strict_types=1);

namespace Spiral\Validation;

use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Validation\Config\ValidatorConfig;

final class ValidationProvider implements ValidationInterface, RulesInterface, SingletonInterface
{
    /** @var RuleInterface[] */
    private array $rules = [];

    public function __construct(
        private ValidatorConfig $config,
        private readonly ParserInterface $parser = new RuleParser(),
        private FactoryInterface $factory = new Container()
    ) {
    }

    /**
     * Destruct the service.
     *
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        unset($this->config, $this->factory);
        $this->resetCache();
    }

    public function validate(array|object $data, array $rules, mixed $context = null): ValidatorInterface
    {
        return new Validator($data, $rules, $context, $this);
    }

    /**
     * Attention, for performance reasons method would cache all defined rules.
     */
    public function getRules(array|string|\Closure $rules): \Generator
    {
        foreach ($this->parser->split($rules) as $id => $rule) {
            if (empty($id) || $rule instanceof \Closure) {
                yield new CallableRule($rule);
                continue;
            }

            // fetch from cache
            if (isset($this->rules[$id])) {
                yield $this->rules[$id];
                continue;
            }

            $function = $this->parser->parseCheck($rule);
            $conditions = $this->parser->parseConditions($rule);

            $check = $this->makeRule(
                $this->config->mapFunction($function),
                $rule
            );

            yield $this->rules[$id] = $check->withConditions($this->makeConditions($conditions));
        }
    }

    /**
     * Reset rules cache.
     *
     * @codeCoverageIgnore
     */
    public function resetCache(): void
    {
        $this->rules = [];
    }

    /**
     * Construct rule object.
     *
     * @throws ContainerException
     */
    protected function makeRule(mixed $check, mixed $rule): RuleInterface
    {
        $args = $this->parser->parseArgs($rule);
        $message = $this->parser->parseMessage($rule);

        if (!\is_array($check)) {
            return new CallableRule($check, $args, $message);
        }

        if (\is_string($check[0]) && $this->config->hasChecker($check[0])) {
            $check[0] = $this->config->getChecker($check[0])->resolve($this->factory);

            return new CheckerRule($check[0], $check[1], $args, $message);
        }

        if (!\is_object($check[0])) {
            $check[0] = (new Autowire($check[0]))->resolve($this->factory);
        }

        return new CallableRule($check, $args, $message);
    }

    /**
     * @throws ContainerException
     */
    protected function makeConditions(array $conditions): ?\SplObjectStorage
    {
        if (empty($conditions)) {
            return null;
        }

        $storage = new \SplObjectStorage();
        foreach ($conditions as $condition => $options) {
            $condition = $this->config->resolveAlias($condition);

            if ($this->config->hasCondition($condition)) {
                $autowire = $this->config->getCondition($condition);
            } else {
                $autowire = new Autowire($condition);
            }

            $storage->attach($autowire->resolve($this->factory), $options);
        }

        return $storage;
    }
}
