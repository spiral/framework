<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Config\PatchInterface;

class EnableCachePatch implements PatchInterface
{
    public function patch(array $config): array
    {
        $config['cache']['enable'] = true;

        return $config;
    }
}
