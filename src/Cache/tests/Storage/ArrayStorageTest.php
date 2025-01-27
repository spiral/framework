<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache\Storage;

use PHPUnit\Framework\TestCase;
use Spiral\Cache\Storage\ArrayStorage;

final class ArrayStorageTest extends TestCase
{
    public const DEFAULT_TTL = 50;

    private ArrayStorage $storage;

    public function testGetsWithExistsValue(): void
    {
        self::assertTrue($this->storage->set('foo', 'bar'));
        self::assertSame('bar', $this->storage->get('foo'));
    }

    public function testGetsWithNonExistsValue(): void
    {
        self::assertNull($this->storage->get('foo'));
    }

    public function testGetsWithNonExistsValueAndCustomDefaultValue(): void
    {
        self::assertSame('baz', $this->storage->get('foo', 'baz'));
    }

    public function testGetsWithExpiredCache(): void
    {
        $this->storage->set('foo', 'bar', 0);
        self::assertSame(\time(), $this->getCacheTtl('foo'));
        self::assertNull($this->storage->get('foo'));
    }

    public function testReplaceExistsValue(): void
    {
        $this->storage->set('foo', 'bar');
        $this->storage->set('foo', 'baz');
        self::assertSame('baz', $this->storage->get('foo'));
    }

    public function testSetsWithDefaultTTL(): void
    {
        $this->storage->set('foo', 'bar');
        self::assertSame(\time() + self::DEFAULT_TTL, $this->getCacheTtl('foo'));
    }

    public function testSetsWithTTLInSeconds(): void
    {
        $this->storage->set('foo', 'bar', 60);

        self::assertSame(\time() + 60, $this->getCacheTtl('foo'));
    }

    public function testSetsWithTTLInDateInterval(): void
    {
        $this->storage->set('foo', 'bar', new \DateInterval('PT30S'));

        self::assertSame(\time() + 30, $this->getCacheTtl('foo'));
    }

    public function testSetsWithTTLInDateTime(): void
    {
        $this->storage->set('foo', 'bar', new \DateTime('+30 seconds'));

        self::assertSame(\time() + 30, $this->getCacheTtl('foo'));
    }

    public function testDeletesExistsValue(): void
    {
        $this->storage->set('foo', 'bar');
        self::assertTrue($this->storage->delete('foo'));
    }

    public function testDeletesNonExistsValue(): void
    {
        self::assertFalse($this->storage->delete('foo'));
    }

    public function testClearsStorage(): void
    {
        $this->storage->set('foo', 'bar');
        $this->storage->set('baz', 'bar');

        $this->storage->clear();

        self::assertTrue($this->isStorageClear());
    }

    public function testGetsMultipleValues(): void
    {
        $this->storage->set('foo', 'bar', 60);
        $this->storage->set('baz', 'bar', 0);

        self::assertSame([
            'foo' => 'bar',
            'bar' => null,
            'baz' => null,
        ], $this->storage->getMultiple(['foo', 'bar', 'baz']));
    }

    public function testGetsMultipleValuesWithDefaultValue(): void
    {
        $this->storage->set('foo', 'bar', 60);
        $this->storage->set('baz', 'bar', 0);

        self::assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'baz',
        ], $this->storage->getMultiple(['foo', 'bar', 'baz'], 'baz'));
    }

    public function testSetsMultipleWithDefaultTtl(): void
    {
        $this->storage->setMultiple([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        self::assertSame('bar', $this->storage->get('foo'));
        self::assertSame('baz', $this->storage->get('bar'));

        self::assertSame(\time() + self::DEFAULT_TTL, $this->getCacheTtl('foo'));
        self::assertSame(\time() + self::DEFAULT_TTL, $this->getCacheTtl('bar'));
    }

    public function testSetsMultipleWithTtlInSeconds(): void
    {
        $this->storage->setMultiple([
            'foo' => 'bar',
            'bar' => 'baz',
        ], 30);

        self::assertSame('bar', $this->storage->get('foo'));
        self::assertSame('baz', $this->storage->get('bar'));

        self::assertSame(\time() + 30, $this->getCacheTtl('foo'));
        self::assertSame(\time() + 30, $this->getCacheTtl('bar'));
    }

    public function testDeletesMultiple(): void
    {
        $this->storage->set('foo', 'bar', 60);
        $this->storage->set('bar', 'bar', 60);

        self::assertTrue($this->storage->deleteMultiple(['foo', 'bar']));
        self::assertNull($this->storage->get('foo'));
        self::assertNull($this->storage->get('bar'));
    }

    public function testDeletesMultipleWithNonExistsCacheKey(): void
    {
        $this->storage->set('foo', 'bar', 60);

        self::assertFalse($this->storage->deleteMultiple(['foo', 'bar']));
        self::assertNull($this->storage->get('foo'));
    }

    public function testHasKey(): void
    {
        $this->storage->set('foo', 'bar', 60);

        self::assertTrue($this->storage->has('foo'));
        self::assertFalse($this->storage->has('bar'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = new ArrayStorage(self::DEFAULT_TTL);
    }

    private function getCacheTtl(string $key): ?int
    {
        $reflection = new \ReflectionClass($this->storage);
        $property = $reflection->getProperty('storage');

        return $property->getValue($this->storage)[$key]['timestamp'] ?? null;
    }

    private function isStorageClear(): bool
    {
        $reflection = new \ReflectionClass($this->storage);
        $property = $reflection->getProperty('storage');

        return $property->getValue($this->storage) === [];
    }
}
