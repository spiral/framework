<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Http\Request;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\ScopeException;
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
 * @property-read HeadersBag $headers
 * @property-read InputBag   $data
 * @property-read InputBag   $query
 * @property-read InputBag   $cookies
 * @property-read FilesBag   $files
 * @property-read ServerBag  $server
 * @property-read InputBag   $attributes
 */
final class InputManager implements SingletonInterface
{
    /**
     * Associations between bags and representing class/request method.
     *
     * @invisible
     * @var array
     */
    protected $bagAssociations = [
        'headers'    => [
            'class'  => HeadersBag::class,
            'source' => 'getHeaders',
        ],
        'data'       => [
            'class'  => InputBag::class,
            'source' => 'getParsedBody',
        ],
        'query'      => [
            'class'  => InputBag::class,
            'source' => 'getQueryParams',
        ],
        'cookies'    => [
            'class'  => InputBag::class,
            'source' => 'getCookieParams',
        ],
        'files'      => [
            'class'  => FilesBag::class,
            'source' => 'getUploadedFiles',
        ],
        'server'     => [
            'class'  => ServerBag::class,
            'source' => 'getServerParams',
        ],
        'attributes' => [
            'class'  => InputBag::class,
            'source' => 'getAttributes',
        ],
    ];
    /**
     * @invisible
     * @var Request
     */
    protected $request;

    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container;

    /** @var InputBag[] */
    private $bags = [];

    /**
     * Prefix to add for each input request.
     *
     * @see self::withPrefix();
     * @var string
     */
    private $prefix = '';

    /**
     * List of content types that must be considered as JSON.
     * @var array
     */
    private $jsonTypes = [
        'application/json',
    ];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @return InputBag
     */
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

    /**
     * Creates new input slice associated with request sub-tree.
     *
     * @param string $prefix
     * @param bool   $add
     * @return InputManager
     */
    public function withPrefix(string $prefix, bool $add = true): self
    {
        $input = clone $this;

        if ($add) {
            $input->prefix .= '.' . $prefix;
            $input->prefix = trim($input->prefix, '.');
        } else {
            $input->prefix = $prefix;
        }

        return $input;
    }

    /**
     * Get page path (including leading slash) associated with active request.
     *
     * @return string
     */
    public function path(): string
    {
        $path = $this->uri()->getPath();

        if (empty($path)) {
            return '/';
        }

        if ($path[0] !== '/') {
            return '/' . $path;
        }

        return $path;
    }

    /**
     * Get UriInterface associated with active request.
     *
     * @return UriInterface
     */
    public function uri(): UriInterface
    {
        return $this->request()->getUri();
    }

    /**
     * Get active instance of ServerRequestInterface and reset all bags if instance changed.
     *
     * @return Request
     *
     * @throws ScopeException
     */
    public function request(): Request
    {
        try {
            $request = $this->container->get(Request::class);
        } catch (ContainerExceptionInterface $e) {
            throw new ScopeException(
                'Unable to get `ServerRequestInterface` in active container scope',
                $e->getCode(),
                $e
            );
        }

        //Flushing input state
        if ($this->request !== $request) {
            $this->bags = [];
            $this->request = $request;
        }

        return $this->request;
    }

    /**
     * Http method. Always uppercase.
     *
     * @return string
     */
    public function method(): string
    {
        return strtoupper($this->request()->getMethod());
    }

    /**
     * Check if request was made over http protocol.
     *
     * @return bool
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
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Check if request was made using XmlHttpRequest.
     *
     * @return bool
     */
    public function isXmlHttpRequest(): bool
    {
        return mb_strtolower(
            $this->request()->getHeaderLine('X-Requested-With')
        ) === 'xmlhttprequest';
    }

    /**
     * Client requesting json response by Accept header.
     *
     * @param bool $softMatch
     * @return bool
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
                $itemValue = strtolower($item->getValue());
                if (str_ends_with($itemValue, '/json') || str_ends_with($itemValue, '+json')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add new content type that will be considered as JSON.
     *
     * @param string $type
     * @return $this
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
     *
     * @return string|null
     */
    public function remoteAddress(): ?string
    {
        $serverParams = $this->request()->getServerParams();

        return $serverParams['REMOTE_ADDR'] ?? null;
    }

    /**
     * Get bag instance or create new one on demand.
     *
     * @param string $name
     * @return InputBag
     */
    public function bag(string $name): InputBag
    {
        // ensure proper request association
        $this->request();

        if (isset($this->bags[$name])) {
            return $this->bags[$name];
        }

        if (!isset($this->bagAssociations[$name])) {
            throw new InputException("Undefined input bag '{$name}'");
        }

        $class = $this->bagAssociations[$name]['class'];
        $data = call_user_func([$this->request(), $this->bagAssociations[$name]['source']]);

        if (!is_array($data)) {
            $data = (array)$data;
        }

        return $this->bags[$name] = new $class($data, $this->prefix);
    }

    /**
     * @param string      $name
     * @param mixed       $default
     * @param bool|string $implode Implode header lines, false to return header as array.
     * @return mixed
     */
    public function header(string $name, $default = null, $implode = ',')
    {
        return $this->headers->get($name, $default, $implode);
    }

    /**
     * @param string $name
     * @param mixed  $default
     * @return mixed
     *
     * @see data()
     */
    public function post(string $name, $default = null)
    {
        return $this->data($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function data(string $name, $default = null)
    {
        return $this->data->get($name, $default);
    }

    /**
     * Reads data from data array, if not found query array will be used as fallback.
     *
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function input(string $name, $default = null)
    {
        return $this->data($name, $this->query($name, $default));
    }

    /**
     * @param string $name
     * @param mixed  $default

     * @return mixed
     */
    public function query(string $name, $default = null)
    {
        return $this->query->get($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default

     * @return mixed
     */
    public function cookie(string $name, $default = null)
    {
        return $this->cookies->get($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return UploadedFileInterface|null
     */
    public function file(string $name, $default = null): ?UploadedFileInterface
    {
        return $this->files->get($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function server(string $name, $default = null)
    {
        return $this->server->get($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function attribute(string $name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }
}
