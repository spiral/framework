<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

interface CasterRegistryInterface
{
    public function register(CasterInterface $caster): void;

    /**
     * @return array<CasterInterface>
     */
    public function getCasters(): array;

    public function getDefault(): CasterInterface;
}
