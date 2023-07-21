<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Fixtures;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\CoreBootloader;

class TestCore extends AbstractKernel
{
    protected const SYSTEM = [
        CoreBootloader::class,
    ];

    protected function bootstrap(): void
    {
    }

    protected function mapDirectories(array $directories): array
    {
        $dir = \dirname(__DIR__) . '/Fixtures';

        return $directories + ['config' => $dir, 'app' => $dir, 'resources' => $dir, 'runtime' => $dir];
    }
}
