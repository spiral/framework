<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

class TopicRegistry implements TopicRegistryInterface
{
    private array $patterns = [];

    public function __construct(array $topics = [])
    {
        foreach ($topics as $topic => $callback) {
            $this->register($topic, $callback);
        }
    }

    public function register(string $topic, callable $callback): void
    {
        $this->patterns[$this->compilePattern($topic)] = $callback;
    }

    public function findCallback(string $topic, array &$matches): ?callable
    {
        foreach ($this->patterns as $pattern => $callback) {
            if (\preg_match($pattern, $topic, $matches)) {
                return $callback;
            }
        }

        return null;
    }

    private function compilePattern(string $topic): string
    {
        $replaces = [];
        if (\preg_match_all('/\{(\w+):?(.*?)?\}/', $topic, $matches)) {
            $variables = \array_combine($matches[1], $matches[2]);
            foreach ($variables as $key => $_) {
                $replaces['{' . $key . '}'] = '(?P<' . $key . '>[^\/\.]+)';
            }
        }

        return '/^' . \strtr($topic, $replaces + ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.']) . '$/iu';
    }
}
