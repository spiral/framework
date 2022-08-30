<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform;

/**
 * Carries list of block definitions between template and import/parent. Provides the ability to
 * track which blocks were claimed.
 */
final class BlockClaims
{
    /** @var array */
    private $claimed = [];

    /** @var array[] */
    private $blocks = [];

    public function __construct(array $blocks)
    {
        $this->blocks = $blocks;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->blocks);
    }

    /**
     * @return mixed|null
     */
    public function get(string $name)
    {
        return $this->blocks[$name] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function claim(string $name)
    {
        $this->claimed[] = $name;

        return $this->get($name);
    }

    public function getNames(): array
    {
        return array_keys($this->blocks);
    }

    public function getClaimed(): array
    {
        return $this->claimed;
    }

    public function getUnclaimed(): array
    {
        return array_diff(array_keys($this->blocks), $this->claimed);
    }
}
