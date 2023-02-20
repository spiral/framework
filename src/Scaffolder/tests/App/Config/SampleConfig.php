<?php

/**
 * {project-name}
 *
 * @author {author-name}
 */

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder\App\Config;

use Spiral\Core\InjectableConfig;

/**
 * Sample Config
 */
class SampleConfig extends InjectableConfig
{
    public const CONFIG = 'sample';

    /** @internal For internal usage. Will be hydrated in the constructor. */
    protected array $config = [];
}
