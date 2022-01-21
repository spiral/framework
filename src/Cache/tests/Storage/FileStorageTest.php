<?php

declare(strict_types=1);

namespace Spiral\Tests\Cache\Storage;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Spiral\Cache\Storage\FileStorage;
use Spiral\Files\Exception\FileNotFoundException;
use Spiral\Files\FilesInterface;

final class FileStorageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    const DEFAULT_TTL = 50;
    const DEFAULT_PATH = 'path/to/cache/0b/ee/0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33';

    /** @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|FilesInterface */
    private $files;

    /** @var FileStorage */
    private $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = \Mockery::mock(FilesInterface::class);
        $this->storage = new FileStorage(
            $this->files,
            'path/to/cache',
            self::DEFAULT_TTL
        );
    }

    public function testGetsWithExistsValueAndCacheFile()
    {
        $ttl = time() + self::DEFAULT_TTL;
        $value = $ttl.'s:3:"bar";';
        $path = self::DEFAULT_PATH;

        $this->files->shouldReceive('read')->with($path)->andReturn($value);

        $this->assertSame('bar', $this->storage->get('foo'));
    }

    public function testGetsWithExistsValueAndNonExistsCacheFile()
    {
        $path = self::DEFAULT_PATH;

        $this->files->shouldReceive('read')
            ->with($path)
            ->andThrow(new FileNotFoundException($path));

        $this->assertNull($this->storage->get('foo'));
    }

    public function testGetsWithExistsValueWithExpiredValue()
    {
        $path = self::DEFAULT_PATH;

        $this->files->shouldReceive('read')->with($path)->andReturn(time().'s:3:"bar";');
        $this->files->shouldReceive('exists')->with($path)->andReturn(true);
        $this->files->shouldReceive('delete')->with($path);

        $this->assertNull($this->storage->get('foo'));
    }

    public function testGetsWithExistsValueWithDeadValue()
    {
        $ttl = time() + self::DEFAULT_TTL;
        $value = $ttl.'s:3:"barbar";';
        $path = self::DEFAULT_PATH;

        $this->files->shouldReceive('read')->with($path)->andReturn($value);
        $this->files->shouldReceive('exists')->with($path)->andReturn(true);
        $this->files->shouldReceive('delete')->with($path);

        $this->assertNull($this->storage->get('foo'));
    }

    public function testSetsWithDefaultTTL()
    {
        $ttl = time() + self::DEFAULT_TTL;
        $value = $ttl.'s:3:"bar";';

        $this->files->shouldReceive('write')->with(self::DEFAULT_PATH, $value, null, true)->andReturnTrue();

        $this->assertTrue($this->storage->set('foo', 'bar'));
    }

    public function testSetsWithTTLInSeconds()
    {
        $ttl = time() + 30;
        $value = $ttl.'s:3:"bar";';

        $this->files->shouldReceive('write')->with(self::DEFAULT_PATH, $value, null, true)->andReturnTrue();

        $this->assertTrue($this->storage->set('foo', 'bar', 30));
    }

    public function testSetsWithTTLInDateInterval()
    {
        $ttl = time() + 30;
        $value = $ttl.'s:3:"bar";';

        $this->files->shouldReceive('write')->with(self::DEFAULT_PATH, $value, null, true)->andReturnTrue();

        $this->assertTrue($this->storage->set('foo', 'bar', new \DateInterval('PT30S')));
    }

    public function testSetsWithTTLInDateTime()
    {
        $ttl = time() + 30;
        $value = $ttl.'s:3:"bar";';

        $this->files->shouldReceive('write')->with(self::DEFAULT_PATH, $value, null, true)->andReturnTrue();

        $this->assertTrue($this->storage->set('foo', 'bar', new \DateTime('+30 seconds')));
    }

    public function testDeleteExistsKey()
    {
        $path = self::DEFAULT_PATH;

        $this->files->shouldReceive('exists')->with($path)->andReturnTrue();
        $this->files->shouldReceive('delete')->with($path);

        $this->storage->delete('foo');
    }

    public function testDeleteNonExistsKey()
    {
        $path = self::DEFAULT_PATH;

        $this->files->shouldReceive('exists')->with($path)->andReturnFalse();
        $this->files->shouldNotReceive('delete');

        $this->storage->delete('foo');
    }

    public function testClearCacheWithExistsDirectory()
    {
        $this->files->shouldReceive('isDirectory')->with('path/to/cache')->andReturnTrue();
        $this->files->shouldReceive('deleteDirectory')->with('path/to/cache');

        $this->assertTrue($this->storage->clear());
    }

    public function testClearCacheWithNotExistsDirectory()
    {
        $this->files->shouldReceive('isDirectory')->with('path/to/cache')->andReturnFalse();
        $this->files->shouldNotReceive('deleteDirectory');

        $this->assertFalse($this->storage->clear());
    }

    public function testGetsMultipleKeys()
    {
        $this->files->shouldReceive('read')->with(
            'path/to/cache/0b/ee/0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33'
        )->andReturn((time() + self::DEFAULT_TTL).'s:3:"abc";');

        $this->files->shouldReceive('read')->with(
            'path/to/cache/62/cd/62cdb7020ff920e5aa642c3d4066950dd1f01f4d'
        )->andReturn((time() + self::DEFAULT_TTL).'s:3:"cde";');

        $this->files->shouldReceive('read')->with(
            'path/to/cache/bb/e9/bbe960a25ea311d21d40669e93df2003ba9b90a2'
        )->andReturn((time() + -1).'s:3:"efg";');
        $this->files->shouldReceive('exists')->with(
            'path/to/cache/bb/e9/bbe960a25ea311d21d40669e93df2003ba9b90a2'
        )->andReturnFalse();

        $this->assertSame([
            'foo' => 'abc',
            'bar' => 'cde',
            'baz' => null,
        ], $this->storage->getMultiple(['foo', 'bar', 'baz']));
    }

    public function testSetsMultipleWithDefaultTTL()
    {
        $ttl = time() + self::DEFAULT_TTL;

        $this->files->shouldReceive('write')
            ->with('path/to/cache/0b/ee/0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', $ttl.'s:3:"baz";', null, true)
            ->andReturnTrue();

        $this->files->shouldReceive('write')
            ->with('path/to/cache/62/cd/62cdb7020ff920e5aa642c3d4066950dd1f01f4d', $ttl.'s:3:"foo";', null, true)
            ->andReturnTrue();

        $this->files->shouldReceive('write')
            ->with('path/to/cache/bb/e9/bbe960a25ea311d21d40669e93df2003ba9b90a2', $ttl.'s:3:"bar";', null, true)
            ->andReturnTrue();

        $this->assertTrue(
            $this->storage->setMultiple([
                'foo' => 'baz',
                'bar' => 'foo',
                'baz' => 'bar',
            ])
        );
    }

    public function testSetsMultipleWithCustomTTL()
    {
        $ttl = time() + 30;

        $this->files->shouldReceive('write')
            ->with('path/to/cache/0b/ee/0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33', $ttl.'s:3:"baz";', null, true)
            ->andReturnTrue();

        $this->files->shouldReceive('write')
            ->with('path/to/cache/62/cd/62cdb7020ff920e5aa642c3d4066950dd1f01f4d', $ttl.'s:3:"foo";', null, true)
            ->andReturnTrue();

        $this->files->shouldReceive('write')
            ->with('path/to/cache/bb/e9/bbe960a25ea311d21d40669e93df2003ba9b90a2', $ttl.'s:3:"bar";', null, true)
            ->andReturnTrue();

        $this->assertTrue(
            $this->storage->setMultiple([
                'foo' => 'baz',
                'bar' => 'foo',
                'baz' => 'bar',
            ], 30)
        );
    }

    public function testSetsMultipleWithFalseResult()
    {
        $this->files->shouldReceive('write')->times(2)->andReturnTrue();
        $this->files->shouldReceive('write')->once()->andReturnFalse();

        $this->assertFalse(
            $this->storage->setMultiple([
                'foo' => 'baz',
                'bar' => 'foo',
                'baz' => 'bar',
            ])
        );
    }

    public function testDeleteMultiple()
    {
        $this->files->shouldReceive('exists')->times(3)->andReturnTrue();
        $this->files->shouldReceive('delete')->times(3)->andReturnTrue();

        $this->assertTrue($this->storage->deleteMultiple(['foo', 'bar', 'baz']));
    }

    public function testDeleteMultipleWithFalseResult()
    {
        $this->files->shouldReceive('exists')->times(3)->andReturnTrue();
        $this->files->shouldReceive('delete')->times(2)->andReturnTrue();
        $this->files->shouldReceive('delete')->once()->andReturnFalse();

        $this->assertFalse($this->storage->deleteMultiple(['foo', 'bar', 'baz']));
    }

    public function testHasCacheValueShouldReturnTrueIfItExists()
    {
        $this->files->shouldReceive('exists')->once()->andReturnTrue();
        $this->assertTrue($this->storage->has('foo'));
    }

    public function testHasCacheValueShouldReturnFalseIfItNotExists()
    {
        $this->files->shouldReceive('exists')->once()->andReturnFalse();
        $this->assertFalse($this->storage->has('foo'));
    }
}
