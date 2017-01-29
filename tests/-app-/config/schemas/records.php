<?php
/**
 * ORM configuration and mapping.
 * - mutators to be automatically applied to record fields based on it's type
 * - mutator aliases to be used in model definitions
 */
use Spiral\Models\Accessors;

return [
    'mutators' => [
        'timestamp'  => ['accessor' => Accessors\SqlTimestamp::class],
        'datetime'   => ['accessor' => Accessors\SqlTimestamp::class],
        'php:int'    => ['setter' => 'intval', 'getter' => 'intval'],
        'php:float'  => ['setter' => 'floatval', 'getter' => 'floatval'],
        'php:string' => ['setter' => 'strval'],
        'php:bool'   => ['setter' => 'boolval', 'getter' => 'boolval'],
        /*{{mutators}}*/
    ],
    'aliases'  => [
        // 'storage' => Accessors\StorageAccessor::class,
        /*{{mutators.aliases}}*/
    ],
];