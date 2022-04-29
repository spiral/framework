<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration\ConfigDeclaration;

class Defaults
{
    private const TYPE_DEFAULTS = [
        'boolean'  => false,
        'bool'     => false,
        'integer'  => 0,
        'int'      => 0,
        'double'   => 0.0,
        'float'    => 0.0,
        'string'   => '',
        'array'    => [],

        //These types aren't forgotten
        'object'   => null,
        'resource' => null,
        'NULL'     => null,
        'null'     => null,
    ];

    public function get(array $values): array
    {
        $output = [];
        foreach ($values as $key => $value) {
            $output[$key] = self::TYPE_DEFAULTS[\gettype($value)] ?? null;
        }

        return $output;
    }
}
