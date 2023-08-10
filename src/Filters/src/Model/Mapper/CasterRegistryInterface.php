<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

interface CasterRegistryInterface
{
    public function register(CasterInterface $setter): void;

    /**
     * @return array<CasterInterface>
     */
    public function getSetters(): array;

    public function getDefault(): CasterInterface;
}
