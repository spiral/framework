<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Reactor;

use PHPUnit\Framework\TestCase;
use ReflectionException;
use Spiral\Reactor\Exception\SerializeException;
use Spiral\Reactor\Partial\Source;
use Spiral\Reactor\Serializer;
use Spiral\Reactor\Traits\SerializerTrait;
use Spiral\Tests\Reactor\Fixture;

class SerializerTest extends TestCase
{
    //To cover this weird trait as well
    use SerializerTrait;

    public function setUp(): void
    {
        $this->setSerializer(new Serializer());
    }

    public function testSetGet(): void
    {
        $this->setSerializer($s = new Serializer());
        $this->assertSame($s, $this->getSerializer());
    }

    /**
     * @throws ReflectionException
     */
    public function testEmptyArray(): void
    {
        $this->assertSame('[]', $this->getSerializer()->serialize([]));
    }

    /**
     * @throws ReflectionException
     */
    public function testArrayOfArray(): void
    {
        $this->assertEquals($this->replace('[
    \'hello\' => [
        \'name\' => 123
    ]
]'), $this->serialized([
            'hello' => ['name' => 123],
        ]));
    }

    /**
     * @param string $value
     * @return string
     */
    private function replace(string $value): string
    {
        return preg_replace('/\s+/', '', $value);
    }

    /**
     * @param $value
     * @return string
     * @throws ReflectionException
     */
    private function serialized($value): string
    {
        return $this->replace($this->getSerializer()->serialize($value));
    }

    /**
     * @throws ReflectionException
     */
    public function testArrayOfArray2(): void
    {
        $this->assertEquals($this->replace('[
    \'hello\' => [
        \'name\' => 123,
        \'sub\'  => magic
    ]
]'), $this->serialized([
            'hello' => ['name' => 123, 'sub' => new Source(['magic'])],
        ]));
    }

    /**
     * @throws ReflectionException
     */
    public function testClassNames(): void
    {
        $this->assertEquals($this->replace('[
    \'hello\' => [
        \'name\' => 123,
        \'sub\'  => \Spiral\Reactor\Serializer::class
    ]
]'), $this->serialized([
            'hello' => ['name' => 123, 'sub' => Serializer::class],
        ]));
    }

    /**
     * @throws ReflectionException
     *
     */
    public function testSerializeResource(): void
    {
        $this->expectException(SerializeException::class);

        $this->getSerializer()->serialize(STDOUT);
    }

    /**
     * @throws ReflectionException
     */
    public function testSerializeObject(): void
    {
        $this->expectException(SerializeException::class);

        $this->getSerializer()->serialize(new Fixture\SerializedObject());
    }

    /**
     * @throws ReflectionException
     */
    public function testSerializeStateObject(): void
    {
        $this->assertEquals(
            $this->replace('\\Spiral\Tests\Reactor\Fixture\SerializedStateObject::__set_state(array())'),
            $this->serialized(new Fixture\SerializedStateObject())
        );
    }
}
