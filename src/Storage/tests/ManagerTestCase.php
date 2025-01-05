<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Storage;
use Spiral\Storage\Bucket;
use Spiral\Storage\Visibility;

#[\PHPUnit\Framework\Attributes\Group('unit')]
class ManagerTestCase extends TestCase
{
    private Storage $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = new Storage();
        $this->manager->add(Storage::DEFAULT_STORAGE, $this->local);
    }

    public function testDefaultResolver(): void
    {
        self::assertSame($this->local, $this->manager->bucket());
    }

    public function testResolverByName(): void
    {
        self::assertSame($this->local, $this->manager->bucket('default'));
    }

    public function testUnknownResolver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket `unknown` has not been defined');

        $this->manager->bucket('unknown');
    }

    public function testAddedResolver(): void
    {
        $this->manager->add('known', $this->second);

        self::assertSame($this->local, $this->manager->bucket());
        self::assertSame($this->second, $this->manager->bucket('known'));
    }

    public function testIterator(): void
    {
        $manager = clone $this->manager;

        $resolvers = \iterator_to_array($manager->getIterator());
        self::assertSame([Storage::DEFAULT_STORAGE => $this->local], $resolvers);

        $manager->add('example', $this->second);

        $resolvers = \iterator_to_array($manager->getIterator());
        self::assertSame([
            Storage::DEFAULT_STORAGE => $this->local,
            'example'                => $this->second
        ], $resolvers);
    }

    public function testCount(): void
    {
        $manager = clone $this->manager;

        self::assertCount(1, $manager);

        $manager->add('example', $this->second);

        self::assertCount(2, $manager);
    }

    public function testInvalidUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'URI argument must be a valid URI in ' .
            '"[STORAGE]://[PATH_TO_FILE]" format, but `test://` given'
        );

        $this->manager->create('test://');

        $this->cleanTempDirectory();
    }

    public function testUriAutoResolvable(): void
    {
        $this->manager->create('file.txt');

        self::assertTrue($this->manager->exists('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testUriWithInvalidStorage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bucket `invalid` has not been defined');

        $this->manager->create('invalid://file.txt');

        $this->cleanTempDirectory();
    }

    public function testCreate(): void
    {
        $this->manager->create('file.txt');

        self::assertTrue($this->manager->exists('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteString(): void
    {
        $content = \random_bytes(64);
        $this->manager->write('file.txt', $content);

        self::assertTrue($this->manager->exists('file.txt'));
        self::assertSame($content, $this->manager->getContents('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteStream(): void
    {
        $content = \random_bytes(64);
        $stream = \fopen('php://memory', 'ab+');
        \fwrite($stream, $content);

        $this->manager->write('file.txt', $stream);

        self::assertTrue($this->manager->exists('file.txt'));
        self::assertSame($content, $this->manager->getContents('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testVisibility(): void
    {
        $this->markTestSkipped(
            'This test [' . __FUNCTION__ . '] returns incorrect visibility ' .
                'of files on Windows OS. ' .
            'It is required to understand the situation'
        );

        $this->manager->create('file.txt');

        $public = Visibility::VISIBILITY_PUBLIC;
        $private = Visibility::VISIBILITY_PRIVATE;

        $this->manager->setVisibility('file.txt', $public);
        self::assertSame($public, $this->manager->getVisibility('file.txt'));

        $this->manager->setVisibility('file.txt', $private);
        self::assertSame($private, $this->manager->getVisibility('file.txt'));
    }

    public function testCopyToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->manager->write('source.txt', $content);
        $this->manager->copy('source.txt', 'copy.txt');

        self::assertTrue($this->manager->exists('source.txt'));
        self::assertSame($content, $this->manager->getContents('source.txt'));

        self::assertTrue($this->manager->exists('copy.txt'));
        self::assertSame($content, $this->manager->getContents('copy.txt'));

        $this->cleanTempDirectory();
    }

    public function testCopyToAnotherStorage(): void
    {
        $manager = clone $this->manager;
        $manager->add('copy', $this->second);

        $content = \random_bytes(64);
        $manager->write('source.txt', $content);
        $manager->copy('source.txt', 'copy://copy.txt');

        self::assertTrue($manager->exists('source.txt'));
        self::assertSame($content, $manager->getContents('source.txt'));
        self::assertFalse($manager->exists('copy.txt'));

        self::assertTrue($manager->exists('copy://copy.txt'));
        self::assertSame($content, $manager->getContents('copy://copy.txt'));
        self::assertFalse($manager->exists('copy://source.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->manager->write('source.txt', $content);
        $this->manager->move('source.txt', 'moved.txt');

        self::assertFalse($this->manager->exists('source.txt'));
        self::assertTrue($this->manager->exists('moved.txt'));
        self::assertSame($content, $this->manager->getContents('moved.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToAnotherStorage(): void
    {
        $manager = clone $this->manager;
        $manager->add('move', $this->second);

        $content = \random_bytes(64);
        $manager->write('source.txt', $content);
        $manager->move('source.txt', 'move://moved.txt');

        self::assertFalse($manager->exists('source.txt'));
        self::assertFalse($manager->exists('moved.txt'));

        self::assertTrue($manager->exists('move://moved.txt'));
        self::assertSame($content, $manager->getContents('move://moved.txt'));
        self::assertFalse($manager->exists('move://source.txt'));

        $this->cleanTempDirectory();
    }

    public function testDelete(): void
    {
        $this->manager->create('file.txt');
        self::assertTrue($this->manager->exists('file.txt'));

        $this->manager->delete('file.txt');
        self::assertFalse($this->manager->exists('file.txt'));
    }

    public function testReadingAsStream(): void
    {
        $content = \random_bytes(64);
        $this->manager->write('file.txt', $content);

        $actual = '';
        $stream = $this->manager->getStream('file.txt');
        while (!\feof($stream)) {
            $actual .= \fread($stream, 256);
        }
        \fclose($stream);

        self::assertSame($actual, $content);

        $this->cleanTempDirectory();
    }

    public function testExisting(): void
    {
        self::assertFalse($this->manager->exists('file.txt'));
        $this->manager->create('file.txt');
        self::assertTrue($this->manager->exists('file.txt'));

        $this->cleanTempDirectory();
    }

    /**
     * Note: This test may fail since it focuses on a mutable value
     */
    public function testLastModified(): void
    {
        $now = \time();

        $this->manager->create('file.txt');
        $before = $this->manager->getLastModified('file.txt');
        self::assertGreaterThanOrEqual($now, $before);

        // Wait 1.1 seconds and then again modify file
        \usleep(1100000);

        $this->manager->write('file.txt', 'content');
        $after = $this->manager->getLastModified('file.txt');
        self::assertGreaterThan($before, $after);
    }

    public function testSize(): void
    {
        $content = \random_bytes(\random_int(32, 256));
        $this->manager->write('file.txt', $content);

        self::assertSame(\strlen($content), $this->manager->getSize('file.txt'));
    }

    /**
     * Note: Checking for all existing mime types is not the goal of this
     *       test; just need to check the readability of the mime type.
     */
    public function testMime(): void
    {
        $this->manager->write('file.txt', 'content');

        self::assertSame('text/plain', $this->manager->getMimeType('file.txt'));
    }
}
