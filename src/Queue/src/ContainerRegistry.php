<?php

declare(strict_types=1);

namespace Spiral\Queue;

use Doctrine\Inflector\Rules\English\InflectorFactory;
use Psr\Container\ContainerInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Queue\Exception\JobException;

final class ContainerRegistry implements HandlerRegistryInterface
{
    /** @var ContainerInterface  */
    private $container;
    /** @var \Doctrine\Inflector\Inflector */
    private $inflector;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->inflector = (new InflectorFactory())->build();
    }

    public function getHandler(string $jobType): HandlerInterface
    {
        try {
            $handler = $this->container->get($this->className($jobType));
        } catch (ContainerException $e) {
            throw new JobException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$handler instanceof HandlerInterface) {
            throw new JobException("Unable to resolve job handler for `{$jobType}`");
        }

        return $handler;
    }

    private function className(string $jobType): string
    {
        $names = explode('.', $jobType);
        $names = array_map(function (string $value) {
            return $this->inflector->classify($value);
        }, $names);

        return join('\\', $names);
    }
}
