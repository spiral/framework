<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Command\Framework;


use Spiral\Framework\ConsoleTest;

class ExtensionsCommand extends ConsoleTest
{
    public function testExtensions()
    {
        $output = $this->runCommand('php:extensions');

        foreach (get_loaded_extensions() as $extension) {
            $this->assertContains($extension, $output);
        }
    }
}