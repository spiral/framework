<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Config\Loader\FileLoaderInterface;

class TestLoader implements FileLoaderInterface
{
    public function loadFile(string $section, string $filename): array
    {
        return [
            'test-key' => 'test value',
        ];
    }
}
