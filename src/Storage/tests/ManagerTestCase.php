<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Storage;
use Spiral\Storage\Bucket;
use Spiral\Storage\Visibility;

/**
 * @group unit
 */
class ManagerTestCase extends TestCase
{
    /**
     * @var Storage
     */
    private $manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = new Storage();
        $this->manager->add(Storage::DEFAULT_STORAGE, $this->local);
    }

    public function testDefaultResolver(): void
    {
        $this->assertSame($this->local, $this->manager->bucket());
    }

    public function testResolverByName(): void
    {
        $this->assertSame($this->local, $this->manager->bucket('default'));
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

        $this->assertSame($this->local, $this->manager->bucket());
        $this->assertSame($this->second, $this->manager->bucket('known'));
    }

    public function testIterator(): void
    {
        $manager = clone $this->manager;

        $resolvers = \iterator_to_array($manager->getIterator());
        $this->assertSame([Storage::DEFAULT_STORAGE => $this->local], $resolvers);

        $manager->add('example', $this->second);

        $resolvers = \iterator_to_array($manager->getIterator());
        $this->assertSame([
            Storage::DEFAULT_STORAGE => $this->local,
            'example'                => $this->second
        ], $resolvers);
    }

    public function testCount(): void
    {
        $manager = clone $this->manager;

        $this->assertSame(1, $manager->count());

        $manager->add('example', $this->second);

        $this->assertSame(2, $manager->count());
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

        $this->assertTrue($this->manager->exists('file.txt'));

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

        $this->assertTrue($this->manager->exists('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteString(): void
    {
        $content = \random_bytes(64);
        $this->manager->write('file.txt', $content);

        $this->assertTrue($this->manager->exists('file.txt'));
        $this->assertSame($content, $this->manager->getContents('file.txt'));

        $this->cleanTempDirectory();
    }

    public function testWriteStream(): void
    {
        $content = \random_bytes(64);
        $stream = \fopen('php://memory', 'ab+');
        \fwrite($stream, $content);

        $this->manager->write('file.txt', $stream);

        $this->assertTrue($this->manager->exists('file.txt'));
        $this->assertSame($content, $this->manager->getContents('file.txt'));

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
        $this->assertSame($public, $this->manager->getVisibility('file.txt'));

        $this->manager->setVisibility('file.txt', $private);
        $this->assertSame($private, $this->manager->getVisibility('file.txt'));
    }

    public function testCopyToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->manager->write('source.txt', $content);
        $this->manager->copy('source.txt', 'copy.txt');

        $this->assertTrue($this->manager->exists('source.txt'));
        $this->assertSame($content, $this->manager->getContents('source.txt'));

        $this->assertTrue($this->manager->exists('copy.txt'));
        $this->assertSame($content, $this->manager->getContents('copy.txt'));

        $this->cleanTempDirectory();
    }

    public function testCopyToAnotherStorage(): void
    {
        $manager = clone $this->manager;
        $manager->add('copy', $this->second);

        $content = \random_bytes(64);
        $manager->write('source.txt', $content);
        $manager->copy('source.txt', 'copy://copy.txt');

        $this->assertTrue($manager->exists('source.txt'));
        $this->assertSame($content, $manager->getContents('source.txt'));
        $this->assertFalse($manager->exists('copy.txt'));

        $this->assertTrue($manager->exists('copy://copy.txt'));
        $this->assertSame($content, $manager->getContents('copy://copy.txt'));
        $this->assertFalse($manager->exists('copy://source.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToSameStorage(): void
    {
        $content = \random_bytes(64);
        $this->manager->write('source.txt', $content);
        $this->manager->move('source.txt', 'moved.txt');

        $this->assertFalse($this->manager->exists('source.txt'));
        $this->assertTrue($this->manager->exists('moved.txt'));
        $this->assertSame($content, $this->manager->getContents('moved.txt'));

        $this->cleanTempDirectory();
    }

    public function testMoveToAnotherStorage(): void
    {
        $manager = clone $this->manager;
        $manager->add('move', $this->second);

        $content = \random_bytes(64);
        $manager->write('source.txt', $content);
        $manager->move('source.txt', 'move://moved.txt');

        $this->assertFalse($manager->exists('source.txt'));
        $this->assertFalse($manager->exists('moved.txt'));

        $this->assertTrue($manager->exists('move://moved.txt'));
        $this->assertSame($content, $manager->getContents('move://moved.txt'));
        $this->assertFalse($manager->exists('move://source.txt'));

        $this->cleanTempDirectory();
    }

    public function testDelete(): void
    {
        $this->manager->create('file.txt');
        $this->assertTrue($this->manager->exists('file.txt'));

        $this->manager->delete('file.txt');
        $this->assertFalse($this->manager->exists('file.txt'));
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

        $this->assertSame($actual, $content);

        $this->cleanTempDirectory();
    }

    public function testExisting(): void
    {
        $this->assertFalse($this->manager->exists('file.txt'));
        $this->manager->create('file.txt');
        $this->assertTrue($this->manager->exists('file.txt'));

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
        $this->assertGreaterThanOrEqual($now, $before);

        // Wait 1.1 seconds and then again modify file
        \usleep(1100000);

        $this->manager->write('file.txt', 'content');
        $after = $this->manager->getLastModified('file.txt');
        $this->assertGreaterThan($before, $after);
    }

    public function testSize(): void
    {
        $content = \random_bytes(\random_int(32, 256));
        $this->manager->write('file.txt', $content);

        $this->assertSame(\strlen($content), $this->manager->getSize('file.txt'));
    }

    /**
     * Note: Checking for all existing mime types is not the goal of this
     *       test; just need to check the readability of the mime type.
     */
    public function testMime(): void
    {
        $this->manager->write('file.txt', 'content');

        $this->assertSame('text/plain', $this->manager->getMimeType('file.txt'));
    }
}
