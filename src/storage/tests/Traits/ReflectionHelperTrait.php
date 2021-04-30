<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage\Traits;

trait ReflectionHelperTrait
{
    /**
     * @template T of object
     * @param T|class-string<T> $object
     * @param string $constant
     * @return mixed
     * @throws \ReflectionException
     *
     * @deprecated Tests should not use this method to call internal implementation
     */
    protected function getNotPublicConst($object, string $constant)
    {
        return (new \ReflectionClass($object))
            ->getConstant($constant)
        ;
    }

    /**
     * @template T of object
     * @param T|class-string<T> $object
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     *
     * @deprecated Tests should not use this method to call internal implementation
     */
    protected function getNotPublicProperty($object, string $property)
    {
        $reflection = new \ReflectionClass($object);

        $protectedProperty = $reflection->getProperty($property);
        $protectedProperty->setAccessible(true);

        return $protectedProperty->getValue($object);
    }

    /**
     * @template T of object
     * @param T|class-string<T> $object
     * @param string $property
     * @param mixed $value
     * @throws \ReflectionException
     *
     * @deprecated Tests should not use this method to call internal implementation
     */
    protected function setNotPublicProperty($object, string $property, $value): void
    {
        $reflection = new \ReflectionClass($object);

        $protectedProperty = $reflection->getProperty($property);
        $protectedProperty->setAccessible(true);

        $protectedProperty->setValue($object, $value);
    }

    /**
     * @template T of object
     * @param T|class-string<T> $object
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     *
     * @deprecated Tests should not use this method to call internal implementation
     */
    protected function callNotPublicMethod($object, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($object);

        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
