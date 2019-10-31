<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Domain;

use Cycle\ORM\ORMInterface;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\Exception\ControllerException;

/**
 * Automatically resolves cycle entities based on given parameter.
 */
final class CycleInterceptor implements CoreInterceptorInterface
{
    /** @var ORMInterface */
    private $orm;

    /** @var array */
    private $entityCache = [];

    /**
     * @param ORMInterface $orm
     */
    public function __construct(ORMInterface $orm)
    {
        $this->orm = $orm;
    }

    /**
     * @inheritDoc
     */
    public function process(string $controller, string $action, array $parameters, CoreInterface $core)
    {
        // todo: support singular ID
        foreach ($this->getDeclaredEntities($controller, $action) as $parameter => $role) {
            if (!isset($parameters[$parameter])) {
                throw new ControllerException(
                    "Entity `{$role}` can not be found",
                    ControllerException::NOT_FOUND
                );
            }

            $entity = $this->orm->getRepository($role)->findByPK($parameters[$parameter]);
            if ($entity === null) {
                throw new ControllerException(
                    "Entity `{$role}` can not be found",
                    ControllerException::NOT_FOUND
                );
            }

            $parameters[$parameter] = $entity;
        }

        return $core->callAction($controller, $action, $parameters);
    }

    /**
     * @param string $controller
     * @param string $action
     * @return array
     */
    private function getDeclaredEntities(string $controller, string $action): array
    {
        $key = sprintf("%s:%s", $controller, $action);
        if (array_key_exists($key, $this->entityCache)) {
            return $this->entityCache[$key];
        }

        $this->entityCache[$key] = [];
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return [];
        }

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getClass() === null) {
                continue;
            }

            if ($this->orm->getSchema()->defines($parameter->getClass()->getName())) {
                $this->entityCache[$key][$parameter->getName()] = $parameter->getClass()->getName();
            }
        }

        return $this->entityCache[$key];
    }
}