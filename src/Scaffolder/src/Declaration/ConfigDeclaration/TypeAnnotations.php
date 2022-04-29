<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration\ConfigDeclaration;

class TypeAnnotations
{
    private const MAPPED_ANNOTATION_TYPES = [
        'boolean' => 'bool',
        'integer' => 'int',
        'double'  => 'float',
        'NULL'    => 'null',
    ];

    private const REAL_ANNOTATION_TYPES = [
        'bool'     => 'bool',
        'int'      => 'int',
        'null'     => 'null',
        'float'    => 'float',
        'string'   => 'string',
        'array'    => 'array',
        'object'   => 'object',
        'resource' => 'resource',
    ];

    public function getAnnotation(mixed $value): string
    {
        if (\is_array($value)) {
            return $this->arrayAnnotationString($value);
        }

        return $this->mapType(\gettype($value));
    }

    public function mapType(string $type): string
    {
        return self::MAPPED_ANNOTATION_TYPES[$type] ?? self::REAL_ANNOTATION_TYPES[$type] ?? 'mixed';
    }

    private function arrayAnnotationString(array $value): string
    {
        $types = [];
        foreach ($value as $item) {
            $types[] = \gettype($item);
        }
        $types = \array_unique($types);

        return \count($types) === 1 ? \sprintf('array|%s[]', $this->mapType($types[0])) : 'array';
    }
}
