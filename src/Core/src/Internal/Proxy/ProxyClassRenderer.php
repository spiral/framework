<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

/**
 * @internal
 */
final class ProxyClassRenderer
{
    /**
     * @param \ReflectionClass $type Interface reflection.
     * @param string $className Class name to use in the generated code.
     * @param bool $defineOverload Define __call() and __callStatic() methods.
     * @param bool $attachContainer Attach container to the proxy.
     *
     * @return non-empty-string PHP code
     */
    public static function renderClass(
        \ReflectionClass $type,
        string $className,
        bool $defineOverload = false,
        bool $attachContainer = false,
    ): string {
        $traits = $defineOverload ? [
            MagicCallTrait::class,
        ] : [];

        if (\str_contains($className, '\\')) {
            $classShortName = \substr($className, \strrpos($className, '\\') + 1);
            $classNamespaceStr = 'namespace ' . \substr($className, 0, \strrpos($className, '\\')) . ';';
        } else {
            $classShortName = $className;
            $classNamespaceStr = '';
        }

        $interface = $type->getName();
        $classBody = [];
        foreach ($type->getMethods() as $method) {
            if ($method->isConstructor()) {
                throw new \LogicException('Constructor is not allowed in a proxy.');
            }

            if ($method->isDestructor()) {
                $classBody[] = self::renderMethod($method);
                continue;
            }

            $hasRefs = false;
            $return = $method->hasReturnType() && (string)$method->getReturnType() === 'void' ? '' : 'return ';
            $call = ($method->isStatic() ? '::' : '->') . $method->getName();
            $context = $method->isStatic() ? 'null' : '$this->__container_proxy_context';
            $containerStr = match (false) {
                $attachContainer => 'null',
                /** @see \Spiral\Core\Internal\Proxy\ProxyTrait::__container_proxy_container */
                $method->isStatic() => '$this->__container_proxy_container',
                default => \sprintf(
                    'throw new \Spiral\Core\Exception\Container\ContainerException(\'%s\')',
                    'Static method call is not allowed on a Proxy that was created without dynamic scope.',
                ),
            };
            $resolveStr = <<<PHP
                \\Spiral\\Core\\Internal\\Proxy\\Resolver::resolve(
                    '{$interface}',
                    {$context},
                    {$containerStr},
                )
                PHP;

            $args = [];
            foreach ($method->getParameters() as $param) {
                $hasRefs = $hasRefs || $param->isPassedByReference();
                $args[] = ($param->isVariadic() ? '...' : '') . '$' . $param->getName();
            }

            if (!$hasRefs && !$method->isVariadic()) {
                $classBody[] = self::renderMethod(
                    $method,
                    <<<PHP
                    {$return}{$resolveStr}{$call}(...\\func_get_args());
                PHP
                );
                continue;
            }

            $argsStr = \implode(', ', $args);

            if ($method->isVariadic()) {
                $classBody[] = self::renderMethod(
                    $method,
                    <<<PHP
                    {$return}{$resolveStr}{$call}($argsStr);
                PHP
                );
                continue;
            }

            $countParams = $method->getNumberOfParameters();
            $classBody[] = self::renderMethod(
                $method,
                <<<PHP
                {$return}{$resolveStr}{$call}($argsStr, ...\\array_slice(\\func_get_args(), {$countParams}));
            PHP
            );
        }
        $bodyStr = \implode("\n\n", $classBody);

        $traitsStr = $traits === [] ? '' : \implode(
            "\n    ",
            \array_map(fn (string $trait): string => 'use \\' . \ltrim($trait, '\\') . ';', $traits)
        );
        return <<<PHP
            $classNamespaceStr

            final class $classShortName implements \\$interface {
                use \Spiral\Core\Internal\Proxy\ProxyTrait;
                $traitsStr

            $bodyStr
            }
            PHP;
    }

    public static function renderMethod(\ReflectionMethod $m, string $body = ''): string
    {
        return \sprintf(
            "public%s function %s%s(%s)%s {\n%s\n}",
            $m->isStatic() ? ' static' : '',
            $m->returnsReference() ? '&' : '',
            $m->getName(),
            \implode(', ', \array_map(self::renderParameter(...), $m->getParameters())),
            $m->hasReturnType()
                ? ': ' . self::renderParameterTypes($m->getReturnType(), $m->getDeclaringClass())
                : '',
            $body,
        );
    }

    public static function renderParameter(\ReflectionParameter $param): string
    {
        return \ltrim(
            \sprintf(
                '%s %s%s%s%s',
                $param->hasType() ? 'mixed' : '',
                $param->isPassedByReference() ? '&' : '',
                $param->isVariadic() ? '...' : '',
                '$' . $param->getName(),
                $param->isOptional() && !$param->isVariadic() ? ' = ' . self::renderDefaultValue($param) : '',
            ),
            ' '
        );
    }

    public static function renderParameterTypes(\ReflectionType $types, \ReflectionClass $class): string
    {
        if ($types instanceof \ReflectionNamedType) {
            return ($types->allowsNull() && $types->getName() !== 'mixed' ? '?' : '') . ($types->isBuiltin()
                    ? $types->getName()
                    : self::normalizeClassType($types, $class));
        }

        [$separator, $types] = match (true) {
            $types instanceof \ReflectionUnionType => ['|', $types->getTypes()],
            $types instanceof \ReflectionIntersectionType => ['&', $types->getTypes()],
            default => throw new \Exception('Unknown type.'),
        };

        $result = [];
        foreach ($types as $type) {
            $result[] = $type->isBuiltin()
                ? $type->getName()
                : self::normalizeClassType($type, $class);
        }

        return \implode($separator, $result);
    }

    public static function renderDefaultValue(\ReflectionParameter $param): string
    {
        if ($param->isDefaultValueConstant()) {
            $result = $param->getDefaultValueConstantName();

            return \explode('::', (string) $result)[0] === 'self'
                ? $result
                : '\\' . $result;
        }

        $cut = self::cutDefaultValue($param);

        return \str_starts_with($cut, 'new ')
            ? $cut
            : \var_export($param->getDefaultValue(), true);
    }

    public static function normalizeClassType(\ReflectionNamedType $type, \ReflectionClass $class): string
    {
        return '\\' . ($type->getName() === 'self' ? $class->getName() : $type->getName());
    }

    private static function cutDefaultValue(\ReflectionParameter $param): string
    {
        $string = (string)$param;

        return \trim(\substr($string, \strpos($string, '=') + 1, -1));
    }
}
