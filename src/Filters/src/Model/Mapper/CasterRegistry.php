<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

final class CasterRegistry implements CasterRegistryInterface
{
    /** @var array<class-string, CasterInterface> */
    private array $casters = [];

    public function __construct(array $casters = [])
    {
        foreach ($casters as $caster) {
            $this->register($caster);
        }
    }

    public function register(CasterInterface $caster): void
    {
        $this->casters[$caster::class] = $caster;
    }

    /**
     * @return array<CasterInterface>
     */
    public function getCasters(): array
    {
        return \array_values($this->casters);
    }

    public function getDefault(): CasterInterface
    {
        return new DefaultCaster();
    }
}
