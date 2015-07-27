<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Singleton;
use Spiral\Http\Bags\FilesBag;
use Spiral\Http\Bags\HeadersBag;
use Spiral\Http\Bags\ServerBag;

/**
 * @property HeadersBag $headers
 * @property InputBag   $data
 * @property InputBag   $query
 * @property InputBag   $cookies
 * @property FilesBag   $files
 * @property ServerBag  $server
 * @property InputBag   $attributes
 */
class InputManager extends Singleton
{
    /**
     * Declaring to IoC that component should be presented as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Container is required to resolve active instance of ServerRequestInterface.
     *
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Cached instance of ServerRequestInterface.
     *
     * @invisible
     * @var ServerRequestInterface
     */
    protected $request = null;

    /**
     * Associations between bags and representing class/request method.
     *
     * @invisible
     * @var array
     */
    protected $bagAssociations = [
        'headers'    => [
            'class'  => HeadersBag::class,
            'source' => 'getHeaders'
        ],
        'data'       => [
            'class'  => InputBag::class,
            'source' => 'getParsedBody'
        ],
        'query'      => [
            'class'  => InputBag::class,
            'source' => 'getQueryParams'
        ],
        'cookies'    => [
            'class'  => InputBag::class,
            'source' => 'getCookieParams'
        ],
        'files'      => [
            'class'  => FilesBag::class,
            'source' => 'getUploadedFiles'
        ],
        'server'     => [
            'class'  => ServerBag::class,
            'source' => 'getServerParams'
        ],
        'attributes' => [
            'class'  => InputBag::class,
            'source' => 'getAttributes'
        ]
    ];

    /**
     * Already constructed input bag instances.
     *
     * @var array|InputBag[]
     */
    protected $bagInstances = [];

    /**
     * Instance of InputManager. Input manager responsible for simplifying access to
     * ServerRequestInterface parameters such as data (post), query, cookies and etc.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get active instance of ServerRequestInterface.
     *
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        //Check if we still pointing to right request
        if ($this->request !== $this->container->get(ServerRequestInterface::class))
        {
            $this->request = null;

            //Our parameter bags has expired
            $this->bagInstances = [];

            //Update instance
            $this->request = $this->container->get(ServerRequestInterface::class);
        }

        return $this->request;
    }

    /**
     * Get UriInterface associated with current request.
     *
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->getRequest()->getUri();
    }

    /**
     * Get page path (including leading slash).
     *
     * @return string
     */
    public function getPath()
    {
        $path = $this->getUri()->getPath();
        if (empty($path))
        {
            return '/';
        }
        elseif ($path[0] !== '/')
        {
            return '/' . $path;
        }

        return $path;
    }

    /**
     * Get HTTP method request was made with.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getRequest()->getMethod();
    }


    /**
     * Check if request was made over http protocol.
     *
     * @return bool
     */
    public function isSecure()
    {
        return $this->getRequest()->getUri()->getScheme() == 'https';
    }

    /**
     * Check if request was made using XmlHttpRequest.
     *
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->getRequest()->getHeaderLine('X-Requested-With')) == 'xmlhttprequest';
    }

    /**
     * Client requesting json response by Accept header.
     *
     * @return bool
     */
    public function isJsonExpected()
    {
        return $this->getRequest()->getHeaderLine('Accept') == 'application/json';
    }

    /**
     * Get remove addr resolved from $_SERVER['REMOTE_ADDR']. Will return null if nothing if key not
     * exists.
     *
     * @return string|null
     */
    public function getRemoteAddress()
    {
        $serverParams = $this->getRequest()->getServerParams();

        return isset($serverParams['REMOTE_ADDR']) ? $serverParams['REMOTE_ADDR'] : null;
    }

    /**
     * Get bag instance by associated name.
     *
     * @param string $name
     * @return InputBag
     */
    public function getBag($name)
    {
        if (isset($this->bagInstances[$name]))
        {
            return $this->bagInstances[$name];
        }

        if (!isset($this->bagAssociations[$name]))
        {
            throw new \RuntimeException("Undefined input bag '{$name}'.");
        }

        $class = $this->bagAssociations[$name]['class'];
        $data = call_user_func([$this->getRequest(), $this->bagAssociations[$name]['source']]);

        return $this->bagInstances[$name] = new $class($data);
    }

    /**
     * Get bag instance by associated name.
     *
     * @param string $name
     * @return InputBag
     */
    public function __get($name)
    {
        return $this->getBag($name);
    }

    /**
     * Fetch value from parsed body.
     *
     * @param string      $name    Key name.
     * @param mixed       $default Default value.
     * @param bool|string $implode Implode header lines, false to return header as array.
     * @return mixed
     */
    public function header($name, $default = null, $implode = ',')
    {
        return $this->headers->get($name, $default, $implode);
    }

    /**
     * Fetch value from parsed body.
     *
     * @param string $name    Key name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function data($name, $default = null)
    {
        return $this->data->get($name, $default);
    }

    /**
     * Fetch value from parsed body (alias for data() method).
     *
     * @see data()
     * @param string $name    Key name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function post($name, $default = null)
    {
        return $this->data($name, $default);
    }

    /**
     * Fetch value from parsed body.
     *
     * @param string $name    Key name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function query($name, $default = null)
    {
        return $this->query->get($name, $default);
    }

    /**
     * Fetch value from parsed body.
     *
     * @param string $name    Key name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function cookie($name, $default = null)
    {
        return $this->cookies->get($name, $default);
    }

    /**
     * Fetch value from parsed body.
     *
     * @param string $name    Key name.
     * @param mixed  $default Default value.
     * @return UploadedFileInterface|null
     */
    public function file($name, $default = null)
    {
        return $this->files->get($name, $default);
    }

    /**
     * Fetch value from parsed body.
     *
     * @param string $name    Key name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function server($name, $default = null)
    {
        return $this->server->get($name, $default);
    }

    /**
     * Fetch value from request attributes (for example activePath or csrf token).
     *
     * @param string $name    Key name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function attribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }
}