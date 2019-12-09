<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Broadcast\Config;

use Spiral\Core\InjectableConfig;

final class WebsocketsConfig extends InjectableConfig
{
    public const CONFIG = 'websockets';

    /** @var array */
    protected $config = [
        'path'            => '',
        'authorizeServer' => null,
        'authorizeTopics' => []
    ];

    /**
     * @return string|null
     */
    public function getPath(): string
    {
        return $this->config['path'];
    }

    /**
     * @return callable|null
     */
    public function getServerCallback(): ?callable
    {
        return $this->config['authorizeServer'];
    }

    /**
     * @return callable[]
     */
    public function getTopicCallbacks(): array
    {
        return $this->config['authorizeTopics'];
    }
}
