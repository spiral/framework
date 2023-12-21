<?php

declare(strict_types=1);

namespace Spiral\Router;

use Cocur\Slugify\Slugify;
use Cocur\Slugify\SlugifyInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Router\Exception\ConstrainException;
use Spiral\Router\Exception\UriHandlerException;
use Spiral\Router\Registry\DefaultPatternRegistry;
use Spiral\Router\Registry\RoutePatternRegistryInterface;

/**
 * UriMatcher provides ability to match and generate uris based on given parameters.
 *
 * @psalm-type Matches = array{controller: non-empty-string, action: non-empty-string, ...}
 */
final class UriHandler
{
    private const HOST_PREFIX = '//';
    private const DEFAULT_SEGMENT = '[^\/]+';
    private const PATTERN_REPLACES = ['/' => '\\/', '[' => '(?:', ']' => ')?', '.' => '\.'];
    private const SEGMENT_REPLACES = ['/' => '\\/', '.' => '\.'];
    private const URI_FIXERS = [
        '[]' => '',
        '[/]' => '',
        '[' => '',
        ']' => '',
        '://' => '://',
        '//' => '/',
    ];

    private ?string $pattern = null;

    private readonly RoutePatternRegistryInterface $patternRegistry;
    private array $constrains = [];
    private array $defaults = [];
    private bool $matchHost = false;
    /** @readonly */
    private string $prefix = '';
    /** @readonly */
    private string $basePath = '/';
    private ?string $compiled = null;
    private ?string $template = null;
    private array $options = [];

    private \Closure $pathSegmentEncoder;

    /**
     * Note: SlugifyInterface will be removed in next major release.
     * @see UriHandler::withPathSegmentEncoder() for more details.
     */
    public function __construct(
        private readonly UriFactoryInterface $uriFactory,
        SlugifyInterface $slugify = null,
        ?RoutePatternRegistryInterface $patternRegistry = null,
    ) {
        $this->patternRegistry = $patternRegistry ?? new DefaultPatternRegistry();

        $slugify ??= new Slugify();
        $this->pathSegmentEncoder = static fn (string $segment): string => $slugify->slugify($segment);
    }

    /**
     * Set custom path segment encoder.
     *
     * @param \Closure(non-empty-string): non-empty-string $callable Callable must accept string and return string.
     */
    public function withPathSegmentEncoder(\Closure $callable): self
    {
        $uriHandler = clone $this;
        $uriHandler->pathSegmentEncoder = $callable;

        return $uriHandler;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @mutation-free
     */
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
     * @mutation-free
     */
    public function withPrefix(string $prefix): self
    {
        $uriHandler = clone $this;
        $uriHandler->compiled = null;
        $uriHandler->prefix = \trim($prefix, '/');

        return $uriHandler;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @mutation-free
     */
    public function withBasePath(string $basePath): self
    {
        if (!\str_ends_with($basePath, '/')) {
            $basePath .= '/';
        }

        $uriHandler = clone $this;
        $uriHandler->compiled = null;
        $uriHandler->basePath = $basePath;

        return $uriHandler;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @mutation-free
     */
    public function withPattern(string $pattern): self
    {
        $uriHandler = clone $this;
        $uriHandler->pattern = $pattern;
        $uriHandler->compiled = null;
        $uriHandler->matchHost = \str_starts_with($pattern, self::HOST_PREFIX);

        return $uriHandler;
    }

    /**
     * @psalm-assert-if-false null $this->compiled
     * @psalm-assert-if-true !null $this->compiled
     * @psalm-assert-if-true !null $this->pattern
     * @psalm-assert-if-true !null $this->template
     */
    public function isCompiled(): bool
    {
        return $this->compiled !== null;
    }

    /**
     * Match given url against compiled template and return matches array or null if pattern does
     * not match.
     *
     * @return Matches|null
     *
     * @psalm-external-mutation-free
     */
    public function match(UriInterface $uri, array $defaults): ?array
    {
        if (!$this->isCompiled()) {
            $this->compile();
        }

        $matches = [];
        if (!\preg_match($this->compiled, $this->fetchTarget($uri), $matches)) {
            return null;
        }

        $matches = \array_intersect_key(
            \array_filter($matches, static fn (string $value) => $value !== ''),
            $this->options
        );

        return \array_merge($this->options, $defaults, $matches);
    }

    /**
     * Generate Uri for a given parameters and default values.
     */
    public function uri(iterable $parameters = [], array $defaults = []): UriInterface
    {
        if (!$this->isCompiled()) {
            $this->compile();
        }

        $parameters = \array_merge(
            $this->options,
            $defaults,
            $this->fetchOptions($parameters, $query)
        );

        foreach ($this->constrains as $key => $_) {
            if (empty($parameters[$key])) {
                throw new UriHandlerException(\sprintf('Unable to generate Uri, parameter `%s` is missing', $key));
            }
        }

        //Uri without empty blocks (pretty stupid implementation)
        $path = $this->interpolate($this->template, $parameters);

        //Uri with added base path and prefix
        $uri = $this->uriFactory->createUri(($this->matchHost ? '' : $this->basePath) . \trim($path, '/'));

        return empty($query) ? $uri : $uri->withQuery(\http_build_query($query));
    }

    /**
     * Fetch uri segments and query parameters.
     *
     * @param array|null $query Query parameters.
     */
    private function fetchOptions(iterable $parameters, ?array &$query): array
    {
        $allowed = \array_keys($this->options);

        $result = [];
        foreach ($parameters as $key => $parameter) {
            if (\is_int($key) && isset($allowed[$key])) {
                // this segment fetched keys from given parameters either by name or by position
                $key = $allowed[$key];
            } elseif (!\array_key_exists($key, $this->options) && \is_array($parameters)) {
                // all additional parameters given in array form can be glued to query string
                $query[$key] = $parameter;
                continue;
            }

            // String must be normalized here
            if (\is_string($parameter) && !\preg_match('/^[a-z\-_0-9]+$/i', $parameter)) {
                $result[$key] = ($this->pathSegmentEncoder)($parameter);
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
            $uriString = \substr($path, \strlen($this->basePath));
            if ($uriString === false) {
                $uriString = '';
            }
        }

        return \trim($uriString, '/');
    }

    /**
     * Compile route matcher into regexp.
     * @psalm-assert !null $this->pattern
     * @psalm-assert !null $this->template
     * @psalm-assert !null $this->compiled
     */
    private function compile(): void
    {
        if ($this->pattern === null) {
            throw new UriHandlerException('Unable to compile UriHandler, pattern is not set');
        }

        $options = [];
        $replaces = [];

        $prefix = \rtrim($this->getPrefix(), '/ ');
        $pattern = \ltrim($this->pattern, '/ ');
        $pattern = $prefix . '/' . $pattern;
        $pattern = \rtrim(\ltrim($pattern, ':/'), '/');

        // correct [/ first occurrence]
        if (\str_starts_with($pattern, '[/')) {
            $pattern = '[' . \substr($pattern, 2);
        }

        if (\preg_match_all('/<(\w+):?(.*?)?>/', $pattern, $matches)) {
            $variables = \array_combine($matches[1], $matches[2]);

            foreach ($variables as $key => $segment) {
                $segment = $this->prepareSegment($key, $segment);
                $replaces[\sprintf('<%s>', $key)] = \sprintf('(?P<%s>%s)', $key, $segment);
                $options[] = $key;
            }
        }

        $template = \preg_replace('/<(\w+):?.*?>/', '<\1>', $pattern);
        $options = \array_fill_keys($options, null);

        foreach ($this->constrains as $key => $value) {
            if ($value instanceof Autofill) {
                // only forces value replacement, not required to be presented as parameter
                continue;
            }

            if (!\array_key_exists($key, $options) && !isset($this->defaults[$key])) {
                throw new ConstrainException(
                    \sprintf(
                        'Route `%s` does not define routing parameter `<%s>`.',
                        $this->pattern,
                        $key
                    )
                );
            }
        }

        $this->compiled = '/^' . \strtr($template, $replaces + self::PATTERN_REPLACES) . '$/iu';
        $this->template = \stripslashes(\str_replace('?', '', $template));
        $this->options = $options;
    }

    /**
     * Interpolate string with given values.
     */
    private function interpolate(string $string, array $values): string
    {
        $replaces = [];
        foreach ($values as $key => $value) {
            $replaces[\sprintf('<%s>', $key)] = match (true) {
                $value instanceof \Stringable || \is_scalar($value) => (string)$value,
                default => '',
            };
        }

        return \strtr($string, $replaces + self::URI_FIXERS);
    }

    /**
     * Prepares segment pattern with given constrains.
     */
    private function prepareSegment(string $name, string $segment): string
    {
        return match (true) {
            $segment !== '' => $this->patternRegistry->all()[$segment] ?? $segment,
            !isset($this->constrains[$name]) => self::DEFAULT_SEGMENT,
            \is_array($this->constrains[$name]) => \implode(
                '|',
                \array_map(fn (string $segment): string => $this->filterSegment($segment), $this->constrains[$name])
            ),
            default => $this->filterSegment((string)$this->constrains[$name])
        };
    }

    private function filterSegment(string $segment): string
    {
        return \strtr($segment, self::SEGMENT_REPLACES);
    }
}
