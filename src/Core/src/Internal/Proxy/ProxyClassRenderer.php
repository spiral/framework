<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

/**
 * @internal
 */
final class ProxyClassRenderer
{
    public static function renderClass(\ReflectionClass $type, $className): string
    {
        $classShortName = \substr($className, \strrpos($className, '\\') + 1);
        $classNamespace = \substr($className, 0, \strrpos($className, '\\'));

        $interface = $type->getName();
        $classBody = [];
        foreach ($type->getMethods() as $method) {
            if ($method->isConstructor()) {
                continue;
            }


            $hasRefs = false;
            $return = $method->hasReturnType() && (string)$method->getReturnType() === 'void' ? '' : 'return ';
            $call = ($method->isStatic() ? '::' : '->') . $method->getName();

            $args = [];
            foreach ($method->getParameters() as $param) {
                $hasRefs = $hasRefs || $param->isPassedByReference();
                $args[] = ($param->isVariadic() ? '...' : '') . '$'. $param->getName();
            }

            if (!$hasRefs) {
                $classBody[] = self::renderMethod($method, <<<PHP
                    {$return}\\Spiral\\Core\\Internal\\Proxy\\Resolver::resolve('{$interface}')
                        {$call}(...\\func_get_args());
                PHP);
                continue;
            }

            $argsStr = \implode(', ', $args);

            if ($method->isVariadic()) {
                $classBody[] = self::renderMethod($method, <<<PHP
                    {$return}\\Spiral\\Core\\Internal\\Proxy\\Resolver::resolve('{$interface}')
                        {$call}($argsStr);
                PHP);
                continue;
            }

            $classBody[] = self::renderMethod($method, <<<PHP
                {$return}\\Spiral\\Core\\Internal\\Proxy\\Resolver::resolve('{$interface}')
                    {$call}($argsStr, ...\\array_slice(\\func_get_args(), {$method->getNumberOfParameters()}));
            PHP);
        }
        $bodyStr = \implode("\n\n", $classBody);

        return <<<PHP
            namespace $classNamespace;

            final class $classShortName implements \\$interface {
                use \Spiral\Core\Internal\Proxy\ProxyTrait;

            $bodyStr
            }
            PHP;
    }

    public static function renderMethod(\ReflectionMethod $m, string $body = ''): string
    {
        return \sprintf(
            "public%s function %s(%s)%s {\n%s\n}",
            $m->isStatic() ? ' static' : '',
            $m->getName(),
            \implode(', ', \array_map([self::class, 'renderParameter'], $m->getParameters())),
            $m->hasReturnType()
                ? ': ' . self::renderParameterTypes($m->getReturnType(), $m->getDeclaringClass())
                : '',
            $body,
        );
    }

    public static function renderParameter(\ReflectionParameter $param): string
    {
        return \ltrim(\sprintf(
            '%s %s%s%s%s',
            $param->hasType() ? self::renderParameterTypes($param->getType(), $param->getDeclaringClass()) : '',
            $param->isPassedByReference() ? '&' : '',
            $param->isVariadic() ? '...' : '',
            '$' . $param->getName(),
            $param->isOptional() && !$param->isVariadic() ? ' = ' . self::renderDefaultValue($param) : '',
        ), ' ');
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

            return \explode('::', $result)[0] === 'self'
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
