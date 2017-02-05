<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Spiral\Core\Loader;
use Spiral\Core\NullMemory;
use Spiral\Tests\BaseTest;
use Spiral\Tests\Core\Fixtures\IsolatedBClass;
use Spiral\Tests\Core\Fixtures\IsolatedCClass;
use Spiral\Tests\Core\Fixtures\IsolatedClass;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Fixtures\SampleInterface;

class LoaderTest extends BaseTest
{
    public function testEnableDisable()
    {
        $this->assertNoLoaders();

        $loader = new Loader(new NullMemory());
        $this->assertTrue($loader->isEnabled());

        $found = false;
        foreach (spl_autoload_functions() as $function) {
            if (is_array($function) && get_class($function[0]) == Loader::class) {
                $found = true;
            }
        }

        $this->assertTrue($found);

        $loader->disable();

        $this->assertNoLoaders();
    }

    public function testReload()
    {
        $this->assertNoLoaders();

        $loader = new Loader(new NullMemory());
        $this->assertTrue($loader->isEnabled());

        $count = count(spl_autoload_functions());

        $loader->reset();
        $loader->enable();

        $this->assertSame($count, count(spl_autoload_functions()));
        $loader->disable();

        $this->assertNoLoaders();
    }

    public function testLoadClass()
    {
        $this->assertNoLoaders();

        $loader = new Loader(new NullMemory(), false);
        $this->assertFalse($loader->isEnabled());

        $loader->enable();
        $this->assertTrue($loader->isEnabled());

        $this->assertFalse($loader->isKnown(IsolatedCClass::class));
        class_exists(IsolatedCClass::class, true);
        $this->assertTrue($loader->isKnown(IsolatedCClass::class));

        $this->assertArrayHasKey(IsolatedCClass::class, $loader->getClasses());

        $loader->disable();

        $this->assertNoLoaders();
    }

    public function testLoadInterface()
    {
        $this->assertNoLoaders();

        $loader = new Loader(new NullMemory(), false);
        $this->assertFalse($loader->isEnabled());

        $loader->enable();
        $this->assertTrue($loader->isEnabled());

        $this->assertFalse($loader->isKnown(SampleInterface::class));
        class_exists(SampleInterface::class, true);
        $this->assertTrue($loader->isKnown(SampleInterface::class));

        $this->assertArrayHasKey(SampleInterface::class, $loader->getClasses());

        $loader->disable();

        $this->assertNoLoaders();
    }

    public function testLoadClassFromMemory()
    {
        $this->assertNoLoaders();

        $this->memory->saveData(Loader::MEMORY, [
            IsolatedClass::class => __DIR__ . '/Fixtures/IsolatedClass.php'
        ]);

        $loader = new Loader($this->memory);
        $this->assertTrue($loader->isEnabled());

        $this->assertFalse($loader->isKnown(IsolatedClass::class));
        class_exists(IsolatedClass::class, true);
        $this->assertTrue($loader->isKnown(IsolatedClass::class));

        $this->assertArrayHasKey(IsolatedClass::class, $loader->getClasses());

        $loader->disable();

        $this->assertNoLoaders();
    }

    public function testLoadClassFromMemoryInvalidLocation()
    {
        $this->assertNoLoaders();

        $this->memory->saveData(Loader::MEMORY, [
            IsolatedBClass::class => __DIR__ . '/Fixtures/IsolatedBClass-broken.php'
        ]);

        $loader = new Loader($this->memory);
        $this->assertTrue($loader->isEnabled());

        $this->assertFalse($loader->isKnown(IsolatedBClass::class));
        class_exists(IsolatedBClass::class, true);
        $this->assertTrue($loader->isKnown(IsolatedBClass::class));

        $this->assertArrayHasKey(IsolatedBClass::class, $loader->getClasses());

        $loader->disable();

        $this->assertNoLoaders();
    }

    private function assertNoLoaders()
    {
        $found = null;
        foreach (spl_autoload_functions() as $function) {
            if (is_array($function) && get_class($function[0]) == Loader::class) {
                spl_autoload_unregister($function);
                $found = $function;
            }
        }

        if (!empty($found)) {
            spl_autoload_unregister($found);

            $retry = null;
            foreach (spl_autoload_functions() as $function) {
                if (is_array($function) && get_class($function[0]) == Loader::class) {
                    spl_autoload_unregister($function);
                    $retry = $function;
                }
            }

            //don't ask
            if (!empty($retry)) {
                $this->fail(
                    'Found UNREGISTRABLE loader:'
                    . get_class($retry[0]) . ':' . hash('crc32', spl_object_hash($retry[0]))
                );
            } else {
                $this->fail(
                    'Found unregistered loader:'
                    . get_class($found[0]) . ':' . hash('crc32', spl_object_hash($found[0]))
                );
            }
        }
    }
}