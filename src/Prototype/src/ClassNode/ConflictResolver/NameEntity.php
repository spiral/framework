<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode\ConflictResolver;

final class NameEntity extends AbstractEntity
{
    public static function createWithSequence(string $name, int $sequence): NameEntity
    {
        $self = new self();
        $self->name = $name;
        $self->sequence = $sequence;

        return $self;
    }

    public static function create(string $name): NameEntity
    {
        $self = new self();
        $self->name = $name;

        return $self;
    }
}
