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

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
final class WebsocketsConfig extends InjectableConfig
{
    public const CONFIG = 'websockets';

    /** @var array */
    protected $config = [
        'path'            => '',
        'authorizeServer' => null,
        'authorizeTopics' => [],
    ];

    /** @var array */
    private $patterns = [];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        foreach ($config['authorizeTopics'] as $topic => $callback) {
            $this->patterns[$this->compilePattern($topic)] = $callback;
        }
    }

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
     * @param string $topic
     * @param array  $matches
     * @return callable|null
     */
    public function findTopicCallback(string $topic, array &$matches): ?callable
    {
        foreach ($this->patterns as $pattern => $callback) {
            if (preg_match($pattern, $topic, $matches)) {
                return $callback;
            }
        }

        return null;
    }

    /**
     * @param string $topic
     * @return string
     */
    private function compilePattern(string $topic): string
    {
        $replaces = [];
        if (preg_match_all('/\{(\w+):?(.*?)?\}/', $topic, $matches)) {
            $variables = array_combine($matches[1], $matches[2]);
            foreach ($variables as $key => $_) {
                $replaces['{' . $key . '}'] = '(?P<' . $key . '>[^\/\.]+)';
            }
        }

        return '/^' . strtr($topic, $replaces + ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.']) . '$/iu';
    }
}
