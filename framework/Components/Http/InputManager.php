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
use Spiral\Components\Files\FileManager;
use Spiral\Core\Component;
use Spiral\Core\Container;

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
     * @var Container
     */
    protected $container = null;

    /**
     * Cached instance of ServerRequestInterface.
     *
     * @var ServerRequestInterface
     */
    protected $request = null;

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
            }
        }

        return $this->request = $this->container->get('request');
    }



    /**
     * Check if request was made using XmlHttpRequest
     *
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->getRequest()->getHeader('X-Requested-With')) == 'xmlhttprequest';
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
        return $this->getRequest()->getHeaderLine('Accept') == 'application/json';
    }
}