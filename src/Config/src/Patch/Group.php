<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config\Patch;

use Spiral\Config\PatchInterface;

final class Group implements PatchInterface
{
    /** @var array|PatchInterface[] */
    private $patches = [];

    public function __construct(PatchInterface ...$patch)
    {
        $this->patches = $patch;
    }

    public function patch(array $config): array
    {
        foreach ($this->patches as $patch) {
            $config = $patch->patch($config);
        }

        return $config;
    }
}
