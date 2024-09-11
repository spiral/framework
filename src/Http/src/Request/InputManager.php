<?php

declare(strict_types=1);

namespace Spiral\Http\Request;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Scope;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\Exception\ScopeException;
use Spiral\Core\Internal\Introspector;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Exception\InputException;
use Spiral\Http\Header\AcceptHeader;

/**
 * Provides simplistic way to access request input data in controllers and can also be used to
 * populate RequestFilters.
 *
 * Attention, this class is singleton based, it reads request from current active container scope!
 *
 * Technically this class can be made as middleware, but due spiral provides container scoping
 * such functionality may be replaces with simple container request routing.
 *
 * @psalm-type InputBagStructure = array{
 *     class: class-string<InputBag>,
 *     source: non-empty-string,
 *     alias?: non-empty-string
 * }
 *
 * @property-read HeadersBag $headers
 * @property-read InputBag   $data
 * @property-read InputBag   $query
 * @property-read InputBag   $cookies
 * @property-read FilesBag   $files
 * @property-read ServerBag  $server
 * @property-read InputBag   $attributes
 *
 * @method mixed header(string $name, mixed $default = null, bool|string $implode = ',')
 * @method mixed data(string $name, mixed $default = null)
 * @method mixed post(string $name, mixed $default = null)
 * @method mixed query(string $name, mixed $default = null)
 * @method mixed cookie(string $name, mixed $default = null)
 * @method UploadedFileInterface|null file(string $name, mixed $default = null)
 * @method mixed server(string $name, mixed $default = null)
 * @method mixed attribute(string $name, mixed $default = null)
 */
#[Singleton]
#[Scope('http-request')]
final class InputManager
{
    /**
     * Associations between bags and representing class/request method.
     *
     * @invisible
     * @var array<non-empty-string, InputBagStructure>
     */
    protected array $bagAssociations = [
        'headers'    => [
            'class'  => HeadersBag::class,
            'source' => 'getHeaders',
            'alias'  => 'header',
        ],
        'data'       => [
            'class'  => InputBag::class,
            'source' => 'getParsedBody',
            'alias'  => 'post',
        ],
        'query'      => [
            'class'  => InputBag::class,
            'source' => 'getQueryParams',
        ],
        'cookies'    => [
            'class'  => InputBag::class,
            'source' => 'getCookieParams',
            'alias'  => 'cookie',
        ],
        'files'      => [
            'class'  => FilesBag::class,
            'source' => 'getUploadedFiles',
            'alias'  => 'file',
        ],
        'server'     => [
            'class'  => ServerBag::class,
            'source' => 'getServerParams',
        ],
        'attributes' => [
            'class'  => InputBag::class,
            'source' => 'getAttributes',
            'alias'  => 'attribute',
        ],
    ];
    /**
     * @invisible
     */
    protected ?Request $request = null;

    /** @var InputBag[] */
    private array $bags = [];

    /**
     * Prefix to add for each input request.
     *
     * @see self::withPrefix();
     */
    private string $prefix = '';

    /**
     * List of content types that must be considered as JSON.
     */
    private array $jsonTypes = [
        'application/json',
    ];

    public function __construct(
        /** @invisible */
        #[Proxy] private readonly ContainerInterface $container,
        /** @invisible */
        HttpConfig $config = new HttpConfig()
    ) {
        $this->bagAssociations = \array_merge($this->bagAssociations, $config->getInputBags());
    }

    public function __get(string $name): InputBag
    {
        return $this->bag($name);
    }

    /**
     * Flushing bag instances when cloned.
     */
    public function __clone()
    {
        $this->bags = [];
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->bag($name)->get(...$arguments);
    }

    /**
     * Creates new input slice associated with request sub-tree.
     */
    public function withPrefix(string $prefix, bool $add = true): self
    {
        $input = clone $this;

        if ($add) {
            $input->prefix .= '.' . $prefix;
            $input->prefix = \trim($input->prefix, '.');
        } else {
            $input->prefix = $prefix;
        }

        return $input;
    }

    /**
     * Get page path (including leading slash) associated with active request.
     */
    public function path(): string
    {
        $path = $this->uri()->getPath();

        return match (true) {
            empty($path) => '/',
            $path[0] !== '/' => '/' . $path,
            default => $path
        };
    }

    /**
     * Get UriInterface associated with active request.
     */
    public function uri(): UriInterface
    {
        return $this->request()->getUri();
    }

    /**
     * Get active instance of ServerRequestInterface and reset all bags if instance changed.
     *
     * @throws ScopeException
     */
    public function request(): Request
    {
        try {
            $request = $this->container->get(Request::class);
        } catch (ContainerExceptionInterface $e) {
            $scope = implode('.', \array_reverse(Introspector::scopeNames($this->container)));
            throw new ScopeException(
                "Unable to get `ServerRequestInterface` in the `$scope` container scope",
                $e->getCode(),
                $e,
            );
        }

        // Flushing input state
        if ($this->request !== $request) {
            $this->bags = [];
            $this->request = $request;
        }

        return $request;
    }

    /**
     * Get the bearer token from the request headers.
     */
    public function bearerToken(): ?string
    {
        $header = (string) $this->header('Authorization', '');

        $position = \strrpos($header, 'Bearer ');

        if ($position !== false) {
            $header = \substr($header, $position + 7);

            return \str_contains($header, ',')
                ? \strstr($header, ',', true)
                : $header;
        }

        return null;
    }

    /**
     * Http method. Always uppercase.
     */
    public function method(): string
    {
        return \strtoupper($this->request()->getMethod());
    }

    /**
     * Check if request was made over http protocol.
     */
    public function isSecure(): bool
    {
        //Double check though attributes?
        return $this->request()->getUri()->getScheme() === 'https';
    }

    /**
     * Check if request was via AJAX.
     * Legacy-support alias for isXmlHttpRequest()
     * @see isXmlHttpRequest()
     */
    public function isAjax(): bool
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Check if request was made using XmlHttpRequest.
     */
    public function isXmlHttpRequest(): bool
    {
        return \mb_strtolower(
            $this->request()->getHeaderLine('X-Requested-With')
        ) === 'xmlhttprequest';
    }

    /**
     * Client requesting json response by Accept header.
     */
    public function isJsonExpected(bool $softMatch = false): bool
    {
        $acceptHeader = AcceptHeader::fromString($this->request()->getHeaderLine('Accept'));
        foreach ($this->jsonTypes as $jsonType) {
            if ($acceptHeader->has($jsonType)) {
                return true;
            }
        }

        if ($softMatch) {
            foreach ($acceptHeader->getAll() as $item) {
                $itemValue = \strtolower((string) $item->getValue());
                if (\str_ends_with($itemValue, '/json') || \str_ends_with($itemValue, '+json')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add new content type that will be considered as JSON.
     */
    public function withJsonType(string $type): self
    {
        $input = clone $this;
        $input->jsonTypes[] = $type;

        return $input;
    }

    /**
     * Get remove addr resolved from $_SERVER['REMOTE_ADDR']. Will return null if nothing if key not
     * exists. Consider using psr-15 middlewares to customize configuration.
     */
    public function remoteAddress(): ?string
    {
        $serverParams = $this->request()->getServerParams();

        return $serverParams['REMOTE_ADDR'] ?? null;
    }

    /**
     * Get bag instance or create new one on demand.
     */
    public function bag(string $name): InputBag
    {
        // ensure proper request association
        $this->request();

        if (isset($this->bags[$name])) {
            return $this->bags[$name];
        }

        $definition = $this->findBagDefinition($name);
        if (!$definition) {
            throw new InputException(\sprintf("Undefined input bag '%s'", $name));
        }

        $class = $definition['class'];
        $data = \call_user_func([$this->request(), $definition['source']]);

        if (!\is_array($data)) {
            $data = (array)$data;
        }

        return $this->bags[$name] = new $class($data, $this->prefix);
    }

    public function hasBag(string $name): bool
    {
        if (isset($this->bags[$name])) {
            return true;
        }

        return \is_array($this->findBagDefinition($name));
    }

    /**
     * Reads data from data array, if not found query array will be used as fallback.
     */
    public function input(string $name, mixed $default = null): mixed
    {
        return $this->data($name, $this->query->get($name, $default));
    }

    /**
     * @return InputBagStructure|null
     */
    private function findBagDefinition(string $name): ?array
    {
        if (isset($this->bagAssociations[$name])) {
            return $this->bagAssociations[$name];
        }

        foreach ($this->bagAssociations as $bag) {
            if (isset($bag['alias']) && $bag['alias'] === $name) {
                return $bag;
            }
        }

        return null;
    }
}
