<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use Spiral\Attributes\Internal\Exception;

/**
 * @internal NamedArgumentsInstantiator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class NamedArgumentsInstantiator extends Instantiator
{
    /**
     * @var string
     */
    private const ERROR_ARGUMENT_NOT_PASSED = '%s::__construct(): Argument #%d ($%s) not passed';

    /**
     * @var string
     */
    private const ERROR_OVERWRITE_ARGUMENT = 'Named parameter $%s overwrites previous argument';

    /**
     * @var string
     */
    private const ERROR_NAMED_ARG_TO_VARIADIC = 'Cannot pass named argument $%s to variadic parameter $%s in PHP < 8';

    /**
     * @var string
     */
    private const ERROR_UNKNOWN_ARGUMENT = 'Unknown named parameter $%s';

    /**
     * @var string
     */
    private const ERROR_POSITIONAL_AFTER_NAMED = 'Cannot use positional argument after named argument';

    /**
     * {@inheritDoc}
     */
    public function instantiate(\ReflectionClass $attr, array $arguments, \Reflector $context = null): object
    {
        if ($this->isNamedArgumentsSupported()) {
            try {
                return $attr->newInstanceArgs($arguments);
            } catch (\Throwable $e) {
                throw Exception::withLocation($e, $attr->getFileName(), $attr->getStartLine());
            }
        }

        $constructor = $this->getConstructor($attr);

        if ($constructor === null) {
            return $attr->newInstanceWithoutConstructor();
        }

        return $attr->newInstanceArgs(
            $this->resolveParameters($attr, $constructor, $arguments)
        );
    }

    private function isNamedArgumentsSupported(): bool
    {
        return \version_compare(\PHP_VERSION, '8.0') >= 0;
    }

    /**
     * @throws \Throwable
     */
    private function resolveParameters(\ReflectionClass $ctx, \ReflectionMethod $constructor, array $arguments): array
    {
        try {
            return $this->doResolveParameters($ctx, $constructor, $arguments);
        } catch (\Throwable $e) {
            throw Exception::withLocation($e, $constructor->getFileName(), $constructor->getStartLine());
        }
    }

    /**
     * @throws \Throwable
     */
    private function doResolveParameters(\ReflectionClass $ctx, \ReflectionMethod $constructor, array $arguments): array
    {
        $namedArgsBegin = $this->analyzeKeys($arguments);

        if ($namedArgsBegin === null) {
            // Only numeric / positional keys exist.
            return $arguments;
        }

        if ($namedArgsBegin === 0) {
            // Only named keys exist.
            $passed = [];
            $named = $arguments;
        } else {
            // Numeric/positional keys followed by named keys.
            // No need to preserve numeric keys.
            $passed = array_slice($arguments, 0, $namedArgsBegin);
            $named = array_slice($arguments, $namedArgsBegin);
        }

        return $this->appendNamedArgs(
            $ctx,
            $passed,
            $named,
            $namedArgsBegin,
            $constructor->getParameters()
        );
    }

    /**
     * Analyzes keys of an arguments array.
     *
     * @param array $arguments Arguments array.
     *        By reference. Numeric keys will be reordered.
     *        Before (success): Mixed numeric keys, then only string keys.
     *        Before (fail): Some string keys are followed by numeric keys.
     *        After (success): Seq. numeric keys starting from 0, then string keys.
     *        After (failure): Seq. numeric keys starting from 0, mixed with string keys.
     *
     * @return int|null Position of the first string key, or NULL if all keys are numeric.
     */
    private function analyzeKeys(array &$arguments): ?int
    {
        // Normalize all numeric keys, but keep string keys.
        $arguments = \array_merge($arguments);

        $i = 0;
        foreach ($arguments as $k => $_) {
            if ($k !== $i) {
                // This must be a string key.
                // Any further numeric keys are illegal.
                if (\array_key_exists($i, $arguments)) {
                    throw new \BadMethodCallException(self::ERROR_POSITIONAL_AFTER_NAMED);
                }
                return $i;
            }
            ++$i;
        }

        // All keys must be numeric.
        return null;
    }

    /**
     * @param array $passed Positional arguments.
     *        Format: $[] = $value.
     * @param array $named Named arguments.
     *        Format: $[$name] = $value.
     * @param int $namedArgsBegin Position of first named argument.
     *        This is identical to count($passed).
     * @param \ReflectionParameter[] $parameters Full list of parameters.
     *
     * @return array Sequential list of all parameter values.
     *         Format: $[] = $value.
     *
     * @throws \Throwable
     *   Arguments provided are incompatible with the parameters.
     */
    private function appendNamedArgs(
        \ReflectionClass $ctx,
        array $passed,
        array $named,
        int $namedArgsBegin,
        array $parameters
    ): array {
        // Analyze parameters.
        $n = count($parameters);
        if ($n > 0 && end($parameters)->isVariadic()) {
            $variadicParameter = end($parameters);
            // Don't include the variadic parameter in the mapping process.
            --$n;
        } else {
            $variadicParameter = null;
        }

        // Process parameters that are not already filled with positional args.
        // This loop will do nothing if $namedArgsBegin >= $n. That's ok.
        for ($i = $namedArgsBegin; $i < $n; ++$i) {
            $parameter = $parameters[$i];
            $k = $parameter->getName();
            if (array_key_exists($k, $named)) {
                $passed[] = $named[$k];
                unset($named[$k]);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $passed[] = $parameter->getDefaultValue();
            } else {
                $message = \vsprintf(self::ERROR_ARGUMENT_NOT_PASSED, [
                    $ctx->getName(),
                    $parameter->getPosition() + 1,
                    $parameter->getName(),
                ]);

                throw new \ArgumentCountError($message);
            }
        }

        if ($named === []) {
            // No unknown argument names exist.
            return $passed;
        }

        // Analyze the first bad argument name, ignore the rest.
        reset($named);
        $badArgName = key($named);

        // Check collision with positional arguments.
        foreach ($parameters as $i => $parameter) {
            if ($i >= $namedArgsBegin) {
                break;
            }
            if ($parameter->getName() === $badArgName) {
                // The named argument overwrites a positional argument.
                $message = \sprintf(self::ERROR_OVERWRITE_ARGUMENT, $badArgName);
                throw new \BadMethodCallException($message);
            }
        }

        // Special handling if a variadic parameter is present.
        if ($variadicParameter !== null) {
            // The last parameter is variadic.
            // Since PHP 8+, variadic parameters can consume named arguments.
            // However, this code only runs if PHP < 8.
            $message = \vsprintf(self::ERROR_NAMED_ARG_TO_VARIADIC, [
                $badArgName,
                $variadicParameter->getName(),
            ]);
            throw new \BadMethodCallException($message);
        }

        // No variadic parameter exists.
        // Unknown named arguments are illegal in this case, even in PHP 8.
        $message = \sprintf(self::ERROR_UNKNOWN_ARGUMENT, $badArgName);
        throw new \BadMethodCallException($message);
    }
}
