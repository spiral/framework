<?php

/**
 * Spiral Framework. Scaffolder
 *
 * @license MIT
 * @author  Valentin V (vvval)
 */

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

    /**
     * @param mixed $value
     * @return string
     */
    public function getAnnotation($value): string
    {
        if (is_array($value)) {
            return $this->arrayAnnotationString($value);
        }

        return $this->mapType(gettype($value));
    }

    /**
     * @param string $type
     * @return string
     */
    public function mapType(string $type): string
    {
        return self::MAPPED_ANNOTATION_TYPES[$type] ?? self::REAL_ANNOTATION_TYPES[$type] ?? 'mixed';
    }

    /**
     * @param array $value
     * @return string
     */
    private function arrayAnnotationString(array $value): string
    {
        $types = [];
        foreach ($value as $item) {
            $types[] = gettype($item);
        }
        $types = array_unique($types);

        return count($types) === 1 ? "array|{$this->mapType($types[0])}[]" : 'array';
    }
}
