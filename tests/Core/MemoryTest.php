<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Database\Injections\Fragment;
use Spiral\Tests\BaseTest;

class MemoryTest extends BaseTest
{
    public function testSaveAndLoad()
    {
        $this->memory->saveData('hello', ['test']);
        $this->assertSame(['test'], $this->memory->loadData('hello'));
    }

    public function testOverwrite()
    {
        $this->memory->saveData('hello', ['test']);
        $this->memory->saveData('hello', ['test2']);
        $this->assertSame(['test2'], $this->memory->loadData('hello'));
    }

    public function testLoadUndefined()
    {
        $this->assertSame(null, $this->memory->loadData('hello'));
    }

    public function testSaveAndLoadSerializable()
    {
        $fragment = new Fragment('SELECT * FROM users');

        $this->memory->saveData('hello', $fragment);
        $this->assertEquals($fragment, $this->memory->loadData('hello'));
    }

    public function testLoadCorrupted()
    {
        file_put_contents($this->memoryFilename('corrupt'), '<?php return ');
        $this->assertSame(null, $this->memory->loadData('corrupt'));
    }

    /**
     * Get extension to use for runtime data or configuration cache.
     *
     * @param string $name Runtime data file name (without extension).
     *
     * @return string
     */
    private function memoryFilename(string $name): string
    {
        $name = strtolower(str_replace(['/', '\\'], '-', $name));

        //Runtime cache
        return directory('cache') . $name . '.php';
    }
}