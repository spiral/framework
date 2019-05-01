<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Cycle\Config;

use Spiral\Core\InjectableConfig;

final class CycleConfig extends InjectableConfig
{
    /** @var array */
    private $config = [
        'loadGenerators'    => [],
        'migrateGenerators' => [],
    ];

    public function loadGenerators(): array
    {
        return $this->config['loadGenerators'];
    }

    public function compileGenerators(): array
    {
        return $this->config['loadGenerators'];
    }
}