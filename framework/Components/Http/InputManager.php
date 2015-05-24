<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Components\Http\Input\FileBag;
use Spiral\Components\Http\Input\HeaderBag;
use Spiral\Components\Http\Input\InputBag;
use Spiral\Components\Http\Input\ServerBag;
use Spiral\Core\Component;
use Spiral\Core\Container;

/**
 * @property HeaderBag  $headers
 * @property InputBag   $data
 * @property InputBag   $query
 * @property InputBag   $cookies
 * @property FileBag    $files
 * @property ServerBag  $server
 */
class InputManager extends Component
{
    /**
     * Component is singleton.
     */
    use Component\SingletonTrait;

    /**
     * Declaring to IoC that component should be presented as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Container is required to resolve active instance of Request.
     *
     * @invisible
     * @var Container
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
    protected $bagAssociations = array(
        'headers' => array(
            'class'  => 'Spiral\Components\Http\Input\HeaderBag',
            'source' => 'getHeaders'
        ),
        'data'    => array(
            'class'  => 'Spiral\Components\Http\Input\InputBag',
            'source' => 'getParsedBody'
        ),
        'query'   => array(
            'class'  => 'Spiral\Components\Http\Input\InputBag',
            'source' => 'getQueryParams'
        ),
        'cookies' => array(
            'class'  => 'Spiral\Components\Http\Input\InputBag',
            'source' => 'getCookieParams'
        ),
        'files'   => array(
            'class'  => 'Spiral\Components\Http\Input\FileBag',
            'source' => 'getUploadedFiles'
        ),
        'server'  => array(
            'class'  => 'Spiral\Components\Http\Input\ServerBag',
            'source' => 'getServerParams'
        )
    );

    /**
     * Already constructed input bag instances.
     *
     * @var array|InputBag[]
     */
    protected $bagInstances = array();

    /**
     * Instance of InputManager. Input manager responsible for simplifying access to
     * ServerRequestInterface parameters such as data (post), query, cookies and etc.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
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
        if (!empty($this->request))
        {
            //Checking if we still pointing to right request
            if ($this->request !== $this->container->get('request'))
            {
                $this->request = null;

                //Our parameter bags has expired
                $this->bagInstances = array();
            }
        }

        return $this->request = $this->container->get('request');
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

        $data = call_user_func(array(
            $this->getRequest(),
            $this->bagAssociations[$name]['source']
        ));

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
     * @return mixed
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
     * Check if request was made using XmlHttpRequest
     *
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->getRequest()->getHeader('X - Requested - With')) == 'xmlhttprequest';
    }

    /**
     * Get remove addr resolved from $_SERVER['REMOTE_ADDR']. Will return null if nothing if key not
     * exists.
     *
     * @return string|null
     */
    public function remoteAddr()
    {
        $serverParams = $this->getRequest()->getServerParams();

        return isset($serverParams['REMOTE_ADDR']) ? $serverParams['REMOTE_ADDR'] : null;
    }

    /**
     * Check if frontend requested json response.
     *
     * @return bool
     */
    public function isJsonExpected()
    {
        return $this->getRequest()->getHeaderLine('Accept') == 'application / json';
    }
}