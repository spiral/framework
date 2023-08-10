<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

interface SetterRegistryInterface
{
    public function register(SetterInterface $setter): void;

    /**
     * @return array<SetterInterface>
     */
    public function getSetters(): array;

    public function getDefault(): SetterInterface;
}
