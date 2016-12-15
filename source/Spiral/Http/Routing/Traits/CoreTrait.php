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
use Spiral\Http\Exceptions\ClientExceptions\BadRequestException;
use Spiral\Http\Exceptions\ClientExceptions\ForbiddenException;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\Routing\RouteInterface;

/**
 * Provides ability to invoke core controllers as endpoint.
 */
trait CoreTrait
{
    /**
     * Core class or class name. By default links to spiral CoreInterface.
     *
     * @invisible
     * @var CoreInterface|string
     */
    private $core = CoreInterface::class;

    /**
     * @param CoreInterface|string $core Core class or class name.
     *
     * @return $this|self|RouteInterface
     */
    public function withCore($core): RouteInterface
    {
        $route = clone $this;
        $route->core = $core;

        return $route;
    }

    /**
     * Internal helper used to create execute controller action using associated core instance.
     *
     * @see CoreInterface
     *
     * @param string $controller
     * @param string $action
     * @param array  $parameters
     * @param array  $scope
     *
     * @return mixed
     * @throws ClientException
     */
    protected function callAction(
        string $controller,
        string $action = null,
        array $parameters = [],
        array $scope = []
    ) {
        try {
            return $this->getCore()->callAction($controller, $action, $parameters, $scope);
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
                //no break
            case ControllerException::NOT_FOUND:
                return new NotFoundException($exception->getMessage());
            case  ControllerException::FORBIDDEN:
                return new ForbiddenException($exception->getMessage());
            default:
                return new BadRequestException($exception->getMessage());
        }
    }

    /**
     * @return CoreInterface
     */
    protected function getCore(): CoreInterface
    {
        if ($this->core instanceof CoreInterface) {
            return $this->core;
        }

        return $this->iocContainer()->get($this->core);
    }

    /**
     * @return ContainerInterface
     */
    abstract protected function iocContainer(): ContainerInterface;
}
