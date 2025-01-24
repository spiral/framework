<?php

declare(strict_types=1);

return [
    'strParam'      => 'runtime',
    'intParam'      => 1,
    'floatParam'    => 2.,
    'boolParam'     => true,
    'nullParam'     => null,
    'arrParam'      => ['a', 'b', 'c'],
    'mapParam'      => ['k1' => 'v1', 'k2' => 'v2'],
    'mixedArrParam' => ['k1' => 'v1', 'k2' => 2],
    'params'        => [
        'default' => 'runtime',
    ],
    'parameters'    => [
        'default'   => [1, 2, 3],
        'primary'   => [4, 5, 6],
        'secondary' => [7, 8, 9],
    ],
    'conflicts'     => [
        'default'   => [1, 2, 3],
        'primary'   => [4, 5, 6],
        'secondary' => [7, 8, 9],
    ],
    'conflict'      => 'some conflicted param',
    'values'        => [
        'default'   => [1, 2, 3],
        'primary'   => [4, 5, 6],
        'secondary' => [7, 8, 9],
    ],
    'value'         => 'conflict value',
    'valueBy'       => 'final conflict value',
];
