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
        return \sprintf(
            '%s %s%s%s%s',
            self::renderParameterTypes($param),
            $param->isPassedByReference() ? '&' : '',
            $param->isVariadic() ? '...' : '',
            '$' . $param->getName(),
            $param->isOptional() && !$param->isVariadic() ? ' = ' . self::renderDefaultValue($param) : '',
        );
    }

    public static function renderParameterTypes(\ReflectionParameter $param): string
    {
        return $param->hasType() ? (string)$param->getType() : '';
    }

    public static function renderDefaultValue(\ReflectionParameter $param): string
    {
        if ($param->isDefaultValueConstant()) {
            $result = $param->getDefaultValueConstantName();

            return \explode('::', $result)[0] === 'self'
                ? $result
                : '\\' . $result;
        }

        $default = $param->getDefaultValue();
        // if (\is_object($default)) {
        //     throw new \Exception('Object default values are not supported');
        // }

        return \var_export($default, true);
    }
}
