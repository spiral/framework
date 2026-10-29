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
        $before = \time();
        $this->storage->set('foo', 'bar', 0);
        $this->assertExpiresAt($before, 0, 'foo');
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
        $before = \time();
        $this->storage->set('foo', 'bar');
        $this->assertExpiresAt($before, self::DEFAULT_TTL, 'foo');
    }

    public function testSetsWithTTLInSeconds(): void
    {
        $before = \time();
        $this->storage->set('foo', 'bar', 60);

        $this->assertExpiresAt($before, 60, 'foo');
    }

    public function testSetsWithTTLInDateInterval(): void
    {
        $before = \time();
        $this->storage->set('foo', 'bar', new \DateInterval('PT30S'));

        $this->assertExpiresAt($before, 30, 'foo');
    }

    public function testSetsWithTTLInDateTime(): void
    {
        $before = \time();
        $this->storage->set('foo', 'bar', new \DateTime('+30 seconds'));

        $this->assertExpiresAt($before, 30, 'foo');
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
        $before = \time();
        $this->storage->setMultiple([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        self::assertSame('bar', $this->storage->get('foo'));
        self::assertSame('baz', $this->storage->get('bar'));

        $this->assertExpiresAt($before, self::DEFAULT_TTL, 'foo');
        $this->assertExpiresAt($before, self::DEFAULT_TTL, 'bar');
    }

    public function testSetsMultipleWithTtlInSeconds(): void
    {
        $before = \time();
        $this->storage->setMultiple([
            'foo' => 'bar',
            'bar' => 'baz',
        ], 30);

        self::assertSame('bar', $this->storage->get('foo'));
        self::assertSame('baz', $this->storage->get('bar'));

        $this->assertExpiresAt($before, 30, 'foo');
        $this->assertExpiresAt($before, 30, 'bar');
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

    /**
     * Range check: {@see time()} may tick between the storage call and the assertion.
     */
    private function assertExpiresAt(int $before, int $ttl, string $key): void
    {
        $timestamp = $this->getCacheTtl($key);

        self::assertGreaterThanOrEqual($before + $ttl, $timestamp);
        self::assertLessThanOrEqual(\time() + $ttl, $timestamp);
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
