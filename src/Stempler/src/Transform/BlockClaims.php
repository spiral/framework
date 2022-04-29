<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform;

/**
 * Carries list of block definitions between template and import/parent. Provides the ability to
 * track which blocks were claimed.
 */
final class BlockClaims
{
    private array $claimed = [];

    public function __construct(
        private readonly array $blocks
    ) {
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->blocks);
    }

    public function get(string $name): mixed
    {
        return $this->blocks[$name] ?? null;
    }

    public function claim(string $name): mixed
    {
        $this->claimed[] = $name;

        return $this->get($name);
    }

    public function getNames(): array
    {
        return \array_keys($this->blocks);
    }

    public function getClaimed(): array
    {
        return $this->claimed;
    }

    public function getUnclaimed(): array
    {
        return \array_diff(\array_keys($this->blocks), $this->claimed);
    }
}
