<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Module;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Module\Exception\PublishException;
use Spiral\Tests\Framework\ConsoleTest;

final class PublishTest extends ConsoleTest
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

        $this->assertFalse(is_file($file));

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        $this->assertFileExists($file);
        $this->assertSame('test', file_get_contents($file));
    }

    public function testReplace(): void
    {
        $this->runCommand('conf');

        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        $this->assertSame('test', file_get_contents($file));
    }

    public function testFollow(): void
    {
        $this->runCommand('conf');
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        $this->assertSame('original', file_get_contents($file));
    }

    public function testInvalid(): void
    {
        $this->expectException(PublishException::class);

        $this->runCommand('conf');
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
        $this->runCommand('conf');
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'readonly'
        ]);

        $this->assertSame('test', file_get_contents($file));
    }

    public function testEnsure(): void
    {
        $dir = $this->getDirectoryByAlias('runtime', 'dir', false);
        $this->assertFalse(is_dir($dir));

        $this->runCommand('publish', [
            'type'   => 'ensure',
            'target' => '@runtime/dir',
        ]);

        $this->assertTrue(is_dir($dir));

        rmdir($dir);
    }

    public function testPublishDirectoryReplace(): void
    {
        $this->runCommand('conf');
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime',
            'source' => __DIR__,
            'mode'   => 'runtime'
        ]);

        $this->assertSame('test', file_get_contents($file));
        $this->assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testPublishDirectoryFollow(): void
    {
        $this->runCommand('conf');
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime',
            'source' => __DIR__,
            'mode'   => 'runtime'
        ]);

        $this->assertSame('original', file_get_contents($file));
        $this->assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testPublishDirectoryReplaceStar(): void
    {
        $this->runCommand('conf');
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'replace',
            'target' => '@runtime',
            'source' => __DIR__ . '/*',
            'mode'   => 'runtime'
        ]);

        $this->assertSame('test', file_get_contents($file));
        $this->assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testPublishDirectoryFollowStar(): void
    {
        $this->runCommand('conf');
        $file = $this->getDirectoryByAlias('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime',
            'source' => __DIR__ . '/*',
            'mode'   => 'runtime'
        ]);

        $this->assertSame('original', file_get_contents($file));
        $this->assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    public function testInvalidFile(): void
    {
        $this->expectException(PublishException::class);

        $this->runCommand('conf');

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

        $this->runCommand('conf');

        $this->runCommand('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE . 'invalid/*',
            'mode'   => 'runtime'
        ]);
    }
}
