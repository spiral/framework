<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

final class CasterRegistry implements CasterRegistryInterface
{
    /** @var array<class-string, CasterInterface> */
    private array $setters = [];

    public function __construct(array $setters = [])
    {
        foreach ($setters as $setter) {
            $this->register($setter);
        }
    }

    public function register(CasterInterface $setter): void
    {
        $this->setters[$setter::class] = $setter;
    }

    /**
     * @return array<CasterInterface>
     */
    public function getSetters(): array
    {
        return \array_values($this->setters);
    }

    public function getDefault(): CasterInterface
    {
        return new DefaultCaster();
    }
}
