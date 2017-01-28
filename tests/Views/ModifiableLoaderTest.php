<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Views;

use Spiral\Tests\BaseTest;
use Spiral\Views\Loaders\FileLoader;
use Spiral\Views\Loaders\ModifiableLoader;

class ModifiableLoaderTest extends BaseTest
{
    public function testWrapping()
    {
        $loader = new FileLoader(
            ['default' => [directory('application') . 'alternative/']],
            $this->files
        );

        $modifiable = new ModifiableLoader(
            $this->views->getEnvironment(),
            $loader
        );

        $this->assertSame($loader->getNamespaces(), $modifiable->getNamespaces());
        $this->assertSame($loader->exists('native.php'), $modifiable->exists('native.php'));

        $modifiable = $modifiable->withExtension('php');

        $this->assertSame($loader->getNamespaces(), $modifiable->getNamespaces());
        $this->assertSame($loader->exists('native.php'), $modifiable->exists('native'));
    }
}