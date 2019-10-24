<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Module;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Framework\ConsoleTest;

class PublishTest extends ConsoleTest
{
    protected const TEST_FILE   = __DIR__ . '/test.txt';
    protected const TEST_FILE_2 = __DIR__ . '/PublishTest.php';

    public function tearDown(): void
    {
        if (file_exists(self::TEST_FILE)) {
            unlink(self::TEST_FILE);
        }

        $this->runCommand('cache:clean');
    }

    public function testPublish(): void
    {
        $file = $this->file('runtime', 'test.txt');
        file_put_contents(self::TEST_FILE, 'test');

        $this->assertFileNotExists($file);

        $this->runCommandDebug('publish', [
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
        $this->runCommandDebug('conf');

        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        $this->assertSame('test', file_get_contents($file));
    }

    public function testFollow(): void
    {
        $this->runCommandDebug('conf');
        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);

        $this->assertSame('original', file_get_contents($file));
    }

    /**
     * @expectedException \Spiral\Module\Exception\PublishException
     */
    public function testInvalid(): void
    {
        $this->runCommandDebug('conf');
        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
            'type'   => 'invalid',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'runtime'
        ]);
    }

    public function testReadonly(): void
    {
        $this->runCommandDebug('conf');
        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
            'type'   => 'replace',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE,
            'mode'   => 'readonly'
        ]);

        $this->assertSame('test', file_get_contents($file));
    }

    public function testEnsure(): void
    {
        $dir = $this->file('runtime', 'dir', false);
        $this->assertFalse(is_dir($dir));

        $this->runCommandDebug('publish', [
            'type'   => 'ensure',
            'target' => '@runtime/dir',
        ]);

        $this->assertTrue(is_dir($dir));

        rmdir($dir);
    }

    public function testPublishDirectoryReplace(): void
    {
        $this->runCommandDebug('conf');
        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
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
        $this->runCommandDebug('conf');
        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
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
        $this->runCommandDebug('conf');
        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
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
        $this->runCommandDebug('conf');
        $file = $this->file('runtime', 'test.txt');
        file_put_contents($file, 'original');
        file_put_contents(self::TEST_FILE, 'test');

        $this->runCommandDebug('publish', [
            'type'   => 'follow',
            'target' => '@runtime',
            'source' => __DIR__ . '/*',
            'mode'   => 'runtime'
        ]);

        $this->assertSame('original', file_get_contents($file));
        $this->assertSame(file_get_contents(__FILE__), file_get_contents(self::TEST_FILE_2));
    }

    /**
     * @expectedException \Spiral\Module\Exception\PublishException
     */
    public function testInvalidFile(): void
    {
        $this->runCommandDebug('conf');

        $this->runCommandDebug('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE . 'invalid',
            'mode'   => 'runtime'
        ]);
    }

    /**
     * @expectedException \Spiral\Module\Exception\PublishException
     */
    public function testInvalidDir(): void
    {
        $this->runCommandDebug('conf');

        $this->runCommandDebug('publish', [
            'type'   => 'follow',
            'target' => '@runtime/test.txt',
            'source' => self::TEST_FILE . 'invalid/*',
            'mode'   => 'runtime'
        ]);
    }

    protected function file(string $dir, string $name)
    {
        return $this->app->get(DirectoriesInterface::class)->get($dir) . $name;
    }
}
