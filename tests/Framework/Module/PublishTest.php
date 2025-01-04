<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Module;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Module\Exception\PublishException;
use Spiral\Tests\Framework\ConsoleTestCase;

final class PublishTest extends ConsoleTestCase
{
    protected const TEST_FILE   = __DIR__ . '/test.txt';
    protected const TEST_FILE_2 = __DIR__ . '/PublishTest.php';

    public function tearDown(): void
    {
        if (file_exists(self::TEST_FILE)) {
            unlink(self::TEST_FILE);
        }

        $this->runCommand('cache:clean');

        parent::tearDown();
    }

    public function testPublish(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents(self::TEST_FILE, 'test');

        self::assertFalse(is_file($file));

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        self::assertFileExists($file);
        self::assertSame('test', file_get_contents($file));
    }

    public function testReplace(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        self::assertSame('test', file_get_contents($file));
    }

    public function testFollow(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        self::assertSame('original', file_get_contents($file));
    }

    public function testInvalid(): void
    {
        $this->expectException(PublishException::class);

        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'invalid',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);
    }

    public function testReadonly(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'readonly'
        ]);

        self::assertSame('test', file_get_contents($file));
    }

    public function testEnsure(): void
    {
        $dir = $this->getDirectoryByAlias('runtime', 'dir');
        self::assertDirectoryDoesNotExist($dir);

        $this->runCommand('publish', [
            'type'   => 'ensure',
            'target' => '@runtime/dir',
        ]);

        self::assertDirectoryExists($dir);

        rmdir($dir);
    }

    public function testPublishDirectoryReplace(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime',
            'source' => __DIR__,
            'mode'   => 'runtime'
        ]);

        self::assertSame('test', file_get_contents($file));
        self::assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testPublishDirectoryFollow(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime',
            'source' => __DIR__,
            'mode'   => 'runtime'
        ]);

        self::assertSame('original', file_get_contents($file));
        self::assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testPublishDirectoryReplaceStar(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime',
            'source' => __DIR__ . '/*',
            'mode'   => 'runtime'
        ]);

        self::assertSame('test', file_get_contents($file));
        self::assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testPublishDirectoryFollowStar(): void
    {
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime',
            'source' => __DIR__ . '/*',
            'mode'   => 'runtime'
        ]);

        self::assertSame('original', file_get_contents($file));
        self::assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testInvalidFile(): void
    {
        $this->expectException(PublishException::class);

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE . 'invalid',
            'mode'   => 'runtime'
        ]);
    }

    public function testInvalidDir(): void
    {
        $this->expectException(PublishException::class);

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE . 'invalid/*',
            'mode'   => 'runtime'
        ]);
    }
}
