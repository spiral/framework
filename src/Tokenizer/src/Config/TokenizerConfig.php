<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tokenizer\Config;

use Spiral\Core\InjectableConfig;

/**
 * Tokenizer component configuration.
 */
final class TokenizerConfig extends InjectableConfig
{
    public const CONFIG = 'tokenizer';

    /**
     * @var array
     */
    protected $config = [
        'directories' => [],
        'exclude'     => [],
    ];

    /**
     * @return array
     */
    public function getDirectories(): array
    {
        return $this->config['directories'] ?? [getcwd()];
    }

    /**
     * @return array
     */
    public function getExcludes(): array
    {
        return $this->config['exclude'] ?? ['vendor', 'tests'];
    }
}
