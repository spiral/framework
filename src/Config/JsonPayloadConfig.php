<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Pavel Z
 */

declare(strict_types=1);

namespace Spiral\Config;

use Spiral\Core\InjectableConfig;

class JsonPayloadConfig extends InjectableConfig
{
    public const CONFIG = 'json-payload';

    /** @var array */
    protected $config = [
        'contentTypes' => [
            'application/json'
        ]
    ];

    /**
     * @return mixed
     */
    public function getContentTypes()
    {
        return $this->config['contentTypes'];
    }
}
