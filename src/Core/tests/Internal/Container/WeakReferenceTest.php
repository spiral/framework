<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Container;

use WeakReference;

final class WeakReferenceTest extends BaseTestCase
{
    public function testAliasNotClassName(): void
    {
        $object = new \stdClass();
        $hash = \spl_object_hash($object);
        $reference = \WeakReference::create($object);

        $this->bind('test-alias', $reference);
        $container = $this->createContainer();

        $this->assertSame($object, $container->get('test-alias'));
        $this->assertSame($hash, \spl_object_hash($container->get('test-alias')));
        unset($object);
        // New object can't be created because classname has not been stored
        $this->assertNull($container->get('test-alias'));
    }

    public function testAliasIsClassName(): void
    {
        $object = new \stdClass();
        $hash = \spl_object_hash($object);
        $reference = \WeakReference::create($object);

        $this->bind(\stdClass::class, $reference);
        $container = $this->createContainer();

        unset($object);
        $result = $container->get(\stdClass::class);
        // new instance created using alias class
        $this->assertInstanceOf(\stdClass::class, $result);
        // it is a new object
        $this->assertNotSame($hash, \spl_object_hash($result));
    }

    public function testAddEmptyWeakRefObject(): void
    {
        // Create empty WeakReference
        $reference = \WeakReference::create(new \stdClass());
        $this->assertNull($reference->get());

        $this->bind(\stdClass::class, $reference);
        $container = $this->createContainer();

        $this->assertInstanceOf(\stdClass::class, $container->get(\stdClass::class));
    }
}
