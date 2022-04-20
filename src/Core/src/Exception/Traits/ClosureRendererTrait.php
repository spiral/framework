<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Traits;

use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

trait ClosureRendererTrait
{
    private function renderClosureSignature(ReflectionFunction $reflection): string
    {
        // return $reflection->__toString();
        $closureParameters = [];
        $append = static function (string &$parameterString, bool $condition, string $postfix): void {
            if ($condition) {
                $parameterString .= $postfix;
            }
        };

        foreach ($reflection->getParameters() as $parameter) {
            $parameterString = '';
            /** @var ReflectionNamedType|ReflectionUnionType|null $type */
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType) {
                $append($parameterString, $parameter->allowsNull(), '?');
                $parameterString .= $type->getName() . ' ';
            } elseif ($type instanceof ReflectionUnionType) {
                $types = $type->getTypes();
                $parameterString .= \implode('|', \array_map(
                        static fn (ReflectionNamedType $r) => $r->getName(),
                        $types
                    )) . ' ';
            } elseif ($type instanceof ReflectionIntersectionType) {
                $types = $type->getTypes();
                $parameterString .= \implode('&', \array_map(
                        static fn (ReflectionNamedType $r) => $r->getName(),
                        $types
                    )) . ' ';
            }
            $append($parameterString, $parameter->isPassedByReference(), '&');
            $append($parameterString, $parameter->isVariadic(), '...');
            $parameterString .= '$' . $parameter->name;
            if ($parameter->isDefaultValueAvailable()) {
                $default = $parameter->getDefaultValue();
                $parameterString .= ' = ' . match (true) {
                        \is_object($default) => 'new ' . $default::class . '(...)',
                        default => \var_export($default, true)
                    };
            }
            $closureParameters[] = $parameterString;
        }
        $static = $reflection->isStatic() ? 'static ' : '';
        return "{$static}function (" . \implode(', ', $closureParameters) . ')';
    }
}
