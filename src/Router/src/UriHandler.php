<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Traversable;
use Closure;
use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Router\Exception\ConstrainException;
use Spiral\Router\Exception\UriHandlerException;

/**
 * UriMatcher provides ability to match and generate uris based on given parameters.
 */
final class UriHandler
{
    private const HOST_PREFIX      = '//';
    private const DEFAULT_SEGMENT  = '[^\/]+';
    private const PATTERN_REPLACES = ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.'];
    private const SEGMENT_REPLACES = ['/' => '\\/', '.' => '\.'];
    private const SEGMENT_TYPES    = [
        'int'     => '\d+',
        'integer' => '\d+',
        'uuid'    => '[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}',
    ];
    private const URI_FIXERS       = [
        '[]'  => '',
        '[/]' => '',
        '['   => '',
        ']'   => '',
        '://' => '://',
        '//'  => '/',
    ];

    private UriFactoryInterface $uriFactory;

    private ?string $pattern = null;

    /** @var SlugifyInterface @internal */
    private SlugifyInterface $slugify;

    private array $constrains = [];

    private array $defaults = [];

    private bool $matchHost = false;

    private string $prefix = '';

    private ?string $compiled = null;

    private ?string $template = null;

    private array $options = [];

    /**
     * @param SlugifyInterface|null $slugify
     */
    public function __construct(
        UriFactoryInterface $uriFactory,
        SlugifyInterface $slugify = null
    ) {
        $this->uriFactory = $uriFactory;
        $this->slugify = $slugify ?? new Slugify();
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function withConstrains(array $constrains, array $defaults = []): self
    {
        $uriHandler = clone $this;
        $uriHandler->compiled = null;
        $uriHandler->constrains = $constrains;
        $uriHandler->defaults = $defaults;

        return $uriHandler;
    }

    public function getConstrains(): array
    {
        return $this->constrains;
    }

    /**
     * @param string $prefix
     */
    public function withPrefix($prefix): self
    {
        $uriHandler = clone $this;
        $uriHandler->compiled = null;
        $uriHandler->prefix = $prefix;

        return $uriHandler;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function withPattern(string $pattern): self
    {
        $uriHandler = clone $this;
        $uriHandler->pattern = $pattern;
        $uriHandler->compiled = null;
        $uriHandler->matchHost = strpos($pattern, self::HOST_PREFIX) === 0;

        return $uriHandler;
    }

    public function isCompiled(): bool
    {
        return $this->compiled !== null;
    }

    /**
     * Match given url against compiled template and return matches array or null if pattern does
     * not match.
     */
    public function match(UriInterface $uri, array $defaults): ?array
    {
        if (!$this->isCompiled()) {
            $this->compile();
        }

        $matches = [];
        if (!preg_match($this->compiled, $this->fetchTarget($uri), $matches)) {
            return null;
        }

        $matches = array_intersect_key($matches, $this->options);

        return array_merge($this->options, $defaults, $matches);
    }

    /**
     * Generate Uri for a given parameters and default values.
     *
     * @param array|Traversable $parameters
     */
    public function uri($parameters = [], array $defaults = []): UriInterface
    {
        if (!$this->isCompiled()) {
            $this->compile();
        }

        $parameters = array_merge(
            $this->options,
            $defaults,
            $this->fetchOptions($parameters, $query)
        );

        foreach ($this->constrains as $key => $_) {
            if (empty($parameters[$key])) {
                throw new UriHandlerException("Unable to generate Uri, parameter `{$key}` is missing");
            }
        }

        //Uri without empty blocks (pretty stupid implementation)
        $path = $this->interpolate($this->template, $parameters);

        //Uri with added prefix
        $uri = $this->uriFactory->createUri(($this->matchHost ? '' : $this->prefix) . trim($path, '/'));

        return empty($query) ? $uri : $uri->withQuery(http_build_query($query));
    }

    /**
     * Fetch uri segments and query parameters.
     *
     * @param Traversable|array $parameters
     * @param array|null         $query Query parameters.
     */
    private function fetchOptions($parameters, &$query): array
    {
        $allowed = array_keys($this->options);

        $result = [];
        foreach ($parameters as $key => $parameter) {
            if (is_numeric($key) && isset($allowed[$key])) {
                // this segment fetched keys from given parameters either by name or by position
                $key = $allowed[$key];
            } elseif (!array_key_exists($key, $this->options) && is_array($parameters)) {
                // all additional parameters given in array form can be glued to query string
                $query[$key] = $parameter;
                continue;
            }

            //String must be normalized here
            if (is_string($parameter) && !preg_match('/^[a-z\-_0-9]+$/i', $parameter)) {
                $result[$key] = $this->slugify->slugify($parameter);
                continue;
            }

            $result[$key] = (string)$parameter;
        }

        return $result;
    }

    /**
     * Part of uri path which is being matched.
     */
    private function fetchTarget(UriInterface $uri): string
    {
        $path = $uri->getPath();

        if (empty($path) || $path[0] !== '/') {
            $path = '/' . $path;
        }

        if ($this->matchHost) {
            $uriString = $uri->getHost() . $path;
        } else {
            $uriString = substr($path, strlen($this->prefix));
            if ($uriString === false) {
                $uriString = '';
            }
        }

        return trim($uriString, '/');
    }

    /**
     * Compile route matcher into regexp.
     */
    private function compile(): void
    {
        if ($this->pattern === null) {
            throw new UriHandlerException('Unable to compile UriHandler, pattern is not set');
        }

        $options = $replaces = [];
        $pattern = rtrim(ltrim($this->pattern, ':/'), '/');

        // correct [/ first occurrence]
        if (strpos($pattern, '[/') === 0) {
            $pattern = '[' . substr($pattern, 2);
        }

        if (preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches)) {
            $variables = array_combine($matches[1], $matches[2]);

            foreach ($variables as $key => $segment) {
                $segment = $this->prepareSegment($key, $segment);
                $replaces["<$key>"] = "(?P<$key>$segment)";
                $options[] = $key;
            }
        }

        $template = preg_replace('/<(\w+):?.*?>/', '<\1>', $pattern);
        $options = array_fill_keys($options, null);

        foreach ($this->constrains as $key => $value) {
            if ($value instanceof Autofill) {
                // only forces value replacement, not required to be presented as parameter
                continue;
            }

            if (!array_key_exists($key, $options) && !isset($this->defaults[$key])) {
                throw new ConstrainException(
                    sprintf(
                        'Route `%s` does not define routing parameter `<%s>`.',
                        $this->pattern,
                        $key
                    )
                );
            }
        }

        $this->compiled = '/^' . strtr($template, $replaces + self::PATTERN_REPLACES) . '$/iu';
        $this->template = stripslashes(str_replace('?', '', $template));
        $this->options = $options;
    }

    /**
     * Interpolate string with given values.
     */
    private function interpolate(string $string, array $values): string
    {
        $replaces = [];
        foreach ($values as $key => $value) {
            $value = (is_array($value) || $value instanceof Closure) ? '' : $value;
            $replaces["<{$key}>"] = is_object($value) ? (string)$value : $value;
        }

        return strtr($string, $replaces + self::URI_FIXERS);
    }

    /**
     * Prepares segment pattern with given constrains.
     */
    private function prepareSegment(string $name, string $segment): string
    {
        if ($segment !== '') {
            return self::SEGMENT_TYPES[$segment] ?? $segment;
        }

        if (!isset($this->constrains[$name])) {
            return self::DEFAULT_SEGMENT;
        }

        if (is_array($this->constrains[$name])) {
            $values = array_map([$this, 'filterSegment'], $this->constrains[$name]);

            return implode('|', $values);
        }

        return $this->filterSegment((string)$this->constrains[$name]);
    }

    private function filterSegment(string $segment): string
    {
        return strtr($segment, self::SEGMENT_REPLACES);
    }
}
