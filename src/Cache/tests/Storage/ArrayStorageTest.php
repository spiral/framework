<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache\Storage;

use PHPUnit\Framework\TestCase;
use Spiral\Cache\Storage\ArrayStorage;

final class ArrayStorageTest extends TestCase
{
    const DEFAULT_TTL = 50;

    /** @var ArrayStorage */
    private $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new ArrayStorage(self::DEFAULT_TTL);
    }

    private function getCacheTtl(string $key): ?int
    {
        $reflection = new \ReflectionClass($this->storage);
        $property = $reflection->getProperty('storage');
        $property->setAccessible(true);

        return $property->getValue($this->storage)[$key]['timestamp'] ?? null;
    }

    private function isStorageClear(): bool
    {
        $reflection = new \ReflectionClass($this->storage);
        $property = $reflection->getProperty('storage');
        $property->setAccessible(true);

        return $property->getValue($this->storage) === [];
    }

    public function testGetsWithExistsValue()
    {
        $this->assertTrue($this->storage->set('foo', 'bar'));
        $this->assertSame('bar', $this->storage->get('foo'));
    }

    public function testGetsWithNonExistsValue()
    {
        $this->assertSame(null, $this->storage->get('foo'));
    }

    public function testGetsWithNonExistsValueAndCustomDefaultValue()
    {
        $this->assertSame('baz', $this->storage->get('foo', 'baz'));
    }

    public function testGetsWithExpiredCache()
    {
        $this->storage->set('foo', 'bar', 0);
        $this->assertSame(time(), $this->getCacheTtl('foo'));
        $this->assertSame(null, $this->storage->get('foo'));
    }

    public function testReplaceExistsValue()
    {
        $this->storage->set('foo', 'bar');
        $this->storage->set('foo', 'baz');
        $this->assertSame('baz', $this->storage->get('foo'));
    }

    public function testSetsWithDefaultTTL()
    {
        $this->storage->set('foo', 'bar');
        $this->assertSame(time() + self::DEFAULT_TTL, $this->getCacheTtl('foo'));
    }

    public function testSetsWithTTLInSeconds()
    {
        $this->storage->set('foo', 'bar', 60);

        $this->assertSame(time() + 60, $this->getCacheTtl('foo'));
    }

    public function testSetsWithTTLInDateInterval()
    {
        $this->storage->set('foo', 'bar', new \DateInterval('PT30S'));

        $this->assertSame(time() + 30, $this->getCacheTtl('foo'));
    }

    public function testSetsWithTTLInDateTime()
    {
        $this->storage->set('foo', 'bar', new \DateTime('+30 seconds'));

        $this->assertSame(time() + 30, $this->getCacheTtl('foo'));
    }

    public function testDeletesExistsValue()
    {
        $this->storage->set('foo', 'bar');
        $this->assertTrue($this->storage->delete('foo'));
    }

    public function testDeletesNonExistsValue()
    {
        $this->assertFalse($this->storage->delete('foo'));
    }

    public function testClearsStorage()
    {
        $this->storage->set('foo', 'bar');
        $this->storage->set('baz', 'bar');

        $this->storage->clear();

        $this->assertTrue($this->isStorageClear());
    }

    public function testGetsMultipleValues()
    {
        $this->storage->set('foo', 'bar', 60);
        $this->storage->set('baz', 'bar', 0);

        $this->assertSame([
            'foo' => 'bar',
            'bar' => null,
            'baz' => null,
        ], $this->storage->getMultiple(['foo', 'bar', 'baz']));
    }

    public function testGetsMultipleValuesWithDefaultValue()
    {
        $this->storage->set('foo', 'bar', 60);
        $this->storage->set('baz', 'bar', 0);

        $this->assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'baz',
        ], $this->storage->getMultiple(['foo', 'bar', 'baz'], 'baz'));
    }

    public function testSetsMultipleWithDefaultTtl()
    {
        $this->storage->setMultiple([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->assertSame('bar', $this->storage->get('foo'));
        $this->assertSame('baz', $this->storage->get('bar'));

        $this->assertSame(time() + self::DEFAULT_TTL, $this->getCacheTtl('foo'));
        $this->assertSame(time() + self::DEFAULT_TTL, $this->getCacheTtl('bar'));
    }

    public function testSetsMultipleWithTtlInSeconds()
    {
        $this->storage->setMultiple([
            'foo' => 'bar',
            'bar' => 'baz',
        ], 30);

        $this->assertSame('bar', $this->storage->get('foo'));
        $this->assertSame('baz', $this->storage->get('bar'));

        $this->assertSame(time() + 30, $this->getCacheTtl('foo'));
        $this->assertSame(time() + 30, $this->getCacheTtl('bar'));
    }

    public function testDeletesMultiple()
    {
        $this->storage->set('foo', 'bar', 60);
        $this->storage->set('bar', 'bar', 60);

        $this->assertTrue($this->storage->deleteMultiple(['foo', 'bar']));
        $this->assertNull($this->storage->get('foo'));
        $this->assertNull($this->storage->get('bar'));
    }

    public function testDeletesMultipleWithNonExistsCacheKey()
    {
        $this->storage->set('foo', 'bar', 60);

        $this->assertFalse($this->storage->deleteMultiple(['foo', 'bar']));
        $this->assertNull($this->storage->get('foo'));
    }

    public function testHasKey()
    {
        $this->storage->set('foo', 'bar', 60);

        $this->assertTrue($this->storage->has('foo'));
        $this->assertFalse($this->storage->has('bar'));
    }
}
