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
    protected const TEST_FILE = __FILE__ . 'test.txt';

    private $remove = [];

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->remove as $file) {
            unlink($file);
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

    protected function file(string $dir, string $name, bool $remove = true)
    {
        $file = $this->app->get(DirectoriesInterface::class)->get($dir) . $name;

        if ($remove) {
            $this->remove[] = $file;
        }

        return $file;
    }
}