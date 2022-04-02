<?php

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode\ConflictResolver;

final class NamespaceEntity extends AbstractEntity
{
    private string $fullName;

    public static function createWithSequence(string $name, string $fullName, int $sequence): NamespaceEntity
    {
        $self = new self();
        $self->name = $name;
        $self->sequence = $sequence;
        $self->fullName = $fullName;

        return $self;
    }

    public static function create(string $name, string $fullName): NamespaceEntity
    {
        $self = new self();
        $self->name = $name;
        $self->fullName = $fullName;

        return $self;
    }

    public function equals(NamespaceEntity $namespace): bool
    {
        return $this->fullName === $namespace->fullName;
    }
}
