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
        $body = [];
        foreach ($type->getMethods() as $method) {
            if ($method->isConstructor()) {
                continue;
            }

            $call = ($method->isStatic() ? '::' : '->') . $method->getName();
            $body[] = self::renderMethod($method, <<<PHP
                    return self::resolve('{$interface}')
                        {$call}(...\\func_get_args());
                PHP);
        }
        $bodyStr = \implode("\n\n", $body);

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
            $m->getReturnType() ? ': ' . $m->getReturnType() : '',
            $body,
        );
    }

    public static function renderParameter(\ReflectionParameter $param): string
    {
        return \ltrim(\sprintf(
            '%s %s%s%s%s',
            self::renderParameterTypes($param),
            $param->isPassedByReference() ? '&' : '',
            $param->isVariadic() ? '...' : '',
            '$' . $param->getName(),
            $param->isOptional() && !$param->isVariadic() ? ' = ' . self::renderDefaultValue($param) : '',
        ), ' ');
    }

    public static function renderParameterTypes(\ReflectionParameter $param): string
    {
        if (!$param->hasType()) {
            return '';
        }

        $types = $param->getType();

        if ($types instanceof \ReflectionNamedType) {
            return ($types->allowsNull() ? '?' : '') . ($types->isBuiltin()
                ? $types->getName()
                : self::normalizeClassType($types, $param));
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
                : self::normalizeClassType($type, $param);
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

    public static function normalizeClassType(\ReflectionNamedType $type, \ReflectionParameter $param): string
    {
        return '\\' . ($type->getName() === 'self' ? $param->getDeclaringClass()->getName() : $type->getName());
    }

    private static function cutDefaultValue(\ReflectionParameter $param): string
    {
        $string = (string)$param;

        return \trim(\substr($string, \strpos($string, '=') + 1, -1));
    }
}
