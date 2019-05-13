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
    protected const TEST_FILE = __DIR__ . '/test.txt';

    public function tearDown()
    {
        if (file_exists(self::TEST_FILE)) {
            unlink(self::TEST_FILE);
        }
    }

    public function testPublish()
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

    public function testReplace()
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

    public function testFollow()
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
    public function testInvalid()
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

    public function testReadonly()
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

    public function testEnsure()
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

    protected function file(string $dir, string $name)
    {
        return $this->app->get(DirectoriesInterface::class)->get($dir) . $name;
    }
}