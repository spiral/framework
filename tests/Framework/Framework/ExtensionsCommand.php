<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Framework;

use Spiral\Tests\Framework\ConsoleTestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class ExtensionsCommand extends ConsoleTestCase
{
    public int $defaultVerbosityLevel = OutputInterface::VERBOSITY_DEBUG;

    public function testExtensions(): void
    {
        $output = $this->runCommand('php:extensions');

        foreach (get_loaded_extensions() as $extension) {
            $this->assertStringContainsString($extension, $output);
        }
    }
}
