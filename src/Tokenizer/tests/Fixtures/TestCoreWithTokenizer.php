<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Fixtures;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Tests\Tokenizer\Fixtures\Bootloader\DirectoryBootloader;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;

class TestCoreWithTokenizer extends AbstractKernel
{
    protected const SYSTEM = [
        CoreBootloader::class,
        TokenizerListenerBootloader::class,
    ];

    protected const LOAD = [
        DirectoryBootloader::class,
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
