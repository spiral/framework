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
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
class CycleInterceptor implements CoreInterceptorInterface
{
    // when only one entity is presented the default parameter will be checked
    protected const DEFAULT_PARAMETER = 'id';

    /** @var ORMInterface @internal */
    protected $orm;

    /** @var array */
    private $cache = [];

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
        $entities = $this->getDeclaredEntities($controller, $action);

        $contextCandidates = [];
        foreach ($entities as $parameter => $role) {
            $value = $this->getParameter($parameter, $parameters, count($entities) === 1);
            if ($value === null) {
                throw new ControllerException(
                    "Entity `{$parameter}` can not be found",
                    ControllerException::NOT_FOUND
                );
            }

            if (is_object($value)) {
                if ($this->orm->getHeap()->has($value)) {
                    $contextCandidates[] = $value;
                }

                // pre-filled
                continue;
            }

            $entity = $this->resolveEntity($role, $value);
            if ($entity === null) {
                throw new ControllerException(
                    "Entity `{$parameter}` can not be found",
                    ControllerException::NOT_FOUND
                );
            }

            $parameters[$parameter] = $entity;
            $contextCandidates[] = $entity;
        }

        if (!isset($parameters['@context']) && count($contextCandidates) === 1) {
            $parameters['@context'] = current($contextCandidates);
        }

        return $core->callAction($controller, $action, $parameters);
    }

    /**
     * @param string $role
     * @param array $parameters
     * @param bool $useDefault
     * @return mixed
     */
    protected function getParameter(string $role, array $parameters, bool $useDefault = false)
    {
        if (!$useDefault) {
            return $parameters[$role] ?? null;
        }

        return $parameters[$role] ?? $parameters[self::DEFAULT_PARAMETER] ?? null;
    }

    /**
     * @param string $role
     * @param mixed $parameter
     * @return object|null
     */
    protected function resolveEntity(string $role, $parameter): ?object
    {
        return $this->orm->getRepository($role)->findByPK($parameter);
    }

    /**
     * @param string $controller
     * @param string $action
     * @return array
     */
    private function getDeclaredEntities(string $controller, string $action): array
    {
        $key = sprintf('%s:%s', $controller, $action);
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $this->cache[$key] = [];
        try {
            $method = new \ReflectionMethod($controller, $action);
        } catch (\ReflectionException $e) {
            return [];
        }

        foreach ($method->getParameters() as $parameter) {
            $class = $this->getParameterClass($parameter);

            if ($class === null) {
                continue;
            }

            if ($this->orm->getSchema()->defines($class->getName())) {
                $this->cache[$key][$parameter->getName()] = $this->orm->resolveRole($class->getName());
            }
        }

        return $this->cache[$key];
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return \ReflectionClass|null
     */
    private function getParameterClass(\ReflectionParameter $parameter): ?\ReflectionClass
    {
        $type = $parameter->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return null;
        }

        if ($type->isBuiltin()) {
            return null;
        }

        return new \ReflectionClass($type->getName());
    }
}
