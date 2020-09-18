<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\ClassNode\ConflictResolver;

final class NamespaceEntity extends AbstractEntity
{
    /** @var string */
    private $fullName;

    /**
     * @param string $name
     * @param string $fullName
     * @param int    $sequence
     * @return NamespaceEntity
     */
    public static function createWithSequence(string $name, string $fullName, int $sequence): NamespaceEntity
    {
        $self = new self();
        $self->name = $name;
        $self->sequence = $sequence;
        $self->fullName = $fullName;

        return $self;
    }

    /**
     * @param string $name
     * @param string $fullName
     * @return NamespaceEntity
     */
    public static function create(string $name, string $fullName): NamespaceEntity
    {
        $self = new self();
        $self->name = $name;
        $self->fullName = $fullName;

        return $self;
    }

    /**
     * @param NamespaceEntity $namespace
     * @return bool
     */
    public function equals(NamespaceEntity $namespace): bool
    {
        return $this->fullName === $namespace->fullName;
    }
}
