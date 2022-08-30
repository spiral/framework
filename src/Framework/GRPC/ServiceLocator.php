<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\GRPC;

use Psr\Container\ContainerInterface;
use Spiral\Tokenizer\ClassesInterface;

/**
 * @deprecated since v2.12. Will be removed in v3.0
 */
final class ServiceLocator implements LocatorInterface
{
    /** @var ClassesInterface */
    private $classes;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ClassesInterface   $classes
     * @param ContainerInterface $container
     */
    public function __construct(ClassesInterface $classes, ContainerInterface $container)
    {
        $this->classes = $classes;
        $this->container = $container;
    }

    /**
     * List all available services.
     *
     * @return array
     */
    public function getServices(): array
    {
        $result = [];
        foreach ($this->classes->getClasses(ServiceInterface::class) as $service) {
            if (!$service->isInstantiable()) {
                continue;
            }

            $instance = $this->container->get($service->getName());

            foreach ($service->getInterfaces() as $interface) {
                if ($interface->isSubclassOf(ServiceInterface::class)) {
                    $result[$interface->getName()] = $instance;
                }
            }
        }

        return $result;
    }
}
