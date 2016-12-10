<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http\Routing\Traits;

use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\ControllerException;
use Spiral\Core\HMVC\CoreInterface;
use Spiral\Http\Exceptions\ClientException;
use Spiral\Http\Routing\RouteInterface;

/**
 * Provides ability to invoke core controllers as endpoint.
 */
trait CoreTrait
{
    /**
     * @invisible
     * @var CoreInterface|null
     */
    private $core;

    /**
     * @param CoreInterface $core
     *
     * @return self|RouteInterface
     */
    public function withCore(CoreInterface $core): RouteInterface
    {
        $route = clone $this;
        $route->core = $core;

        return $route;
    }

    /**
     * Internal helper used to create execute controller action using associated core instance.
     *
     * @param string $controller
     * @param string $action
     * @param array  $parameters
     *
     * @return mixed
     * @throws ClientException
     */
    protected function callAction(string $controller, string $action = null, array $parameters = [])
    {
        try {
            return $this->getCore()->callAction($controller, $action, $parameters);
        } catch (ControllerException $e) {
            throw $this->convertException($e);
        }
    }

    /**
     * Converts controller exceptions into client exceptions.
     *
     * @param ControllerException $exception
     *
     * @return ClientException
     */
    protected function convertException(ControllerException $exception): ClientException
    {
        switch ($exception->getCode()) {
            case ControllerException::BAD_ACTION:
            case ControllerException::NOT_FOUND:
                return new ClientException(ClientException::NOT_FOUND, $exception->getMessage());
            case  ControllerException::FORBIDDEN:
                return new ClientException(ClientException::FORBIDDEN, $exception->getMessage());
            default:
                return new ClientException(ClientException::BAD_DATA, $exception->getMessage());
        }
    }

    /**
     * @return CoreInterface
     */
    protected function getCore(): CoreInterface
    {
        if (!empty($this->core)) {
            return $this->core;
        }

        return $this->iocContainer()->get(CoreInterface::class);
    }

    /**
     * @return ContainerInterface
     */
    abstract protected function iocContainer();
}
