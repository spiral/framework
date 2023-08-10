<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

final class SetterRegistry implements SetterRegistryInterface
{
    /** @var array<class-string, SetterInterface> */
    private array $setters = [];

    public function __construct(array $setters = [])
    {
        foreach ($setters as $setter) {
            $this->register($setter);
        }
    }

    public function register(SetterInterface $setter): void
    {
        $this->setters[$setter::class] = $setter;
    }

    /**
     * @return array<SetterInterface>
     */
    public function getSetters(): array
    {
        return \array_values($this->setters);
    }

    public function getDefault(): SetterInterface
    {
        return new Scalar();
    }
}
