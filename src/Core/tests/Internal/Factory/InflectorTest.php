<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Factory;

use DateTimeImmutable;
use DateTimeInterface;
use Spiral\Core\Config\Inflector;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\LightEngine;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;
use stdClass;

final class InflectorTest extends BaseTestCase
{
    public function testInflectStdClass(): void
    {
        $this->bind(
            stdClass::class,
            new Inflector(
                static function (stdClass $object): stdClass {
                    $object->foo = 'bar';

                    return $object;
                }
            ),
        );

        $object = $this->make(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $object);
        $this->assertObjectHasProperty('foo', $object);
        $this->assertSame($object->foo, 'bar');
    }

    public function testInflectAutowiring(): void
    {
        $this->bind(DateTimeInterface::class, $time = new DateTimeImmutable());
        $this->bind(
            stdClass::class,
            new Inflector(
                static function (stdClass $object, DateTimeInterface $time): stdClass {
                    $object->time = $time;

                    return $object;
                }
            ),
        );

        $object = $this->make(stdClass::class);

        $this->assertInstanceOf(stdClass::class, $object);
        $this->assertObjectHasProperty('time', $object);
        $this->assertSame($time, $object->time);
    }

    public function testFewObjectsUsingAbstractParent(): void
    {
        $this->bind(
            LightEngine::class,
            new Inflector(
                static function (LightEngine $object): LightEngine {
                    return $object->withPower(999999);
                }
            ),
        );

        $object1 = $this->make(EngineVAZ2101::class);
        $object2 = $this->make(EngineMarkTwo::class);
        $this->make(EngineZIL130::class);

        $this->assertSame(999999, $object1->getPower());
        $this->assertSame(999999, $object2->getPower());
    }

    public function testFewObjectsUsingInterface(): void
    {
        $result = [];
        $this->bind(
            MadeInUssrInterface::class,
            new Inflector(
                static function (MadeInUssrInterface $object) use (&$result): MadeInUssrInterface {
                    $result[] = $object;

                    return $object;
                }
            ),
        );

        $object1 = $this->make(EngineVAZ2101::class);
        $this->make(EngineMarkTwo::class);
        $object3 = $this->make(EngineZIL130::class);

        $this->assertSame([$object1, $object3], $result);
    }

    public function testMultipleInflector(): void
    {
        $result1 = $result2 = [];
        $this->bind(
            MadeInUssrInterface::class,
            new Inflector(
                static function (MadeInUssrInterface $object) use (&$result1): MadeInUssrInterface {
                    $result1[] = $object;

                    return $object;
                }
            ),
        );
        $this->bind(
            MadeInUssrInterface::class,
            new Inflector(
                static function (MadeInUssrInterface $object) use (&$result2): MadeInUssrInterface {
                    $result2[] = $object;

                    return $object;
                }
            ),
        );

        $object1 = $this->make(EngineVAZ2101::class);
        $this->make(EngineMarkTwo::class);
        $object3 = $this->make(EngineZIL130::class);

        $this->assertSame([$object1, $object3], $result1);
        $this->assertSame([$object1, $object3], $result2);
    }
}
