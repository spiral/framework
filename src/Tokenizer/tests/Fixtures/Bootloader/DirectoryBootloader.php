<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer\Fixtures\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Tokenizer\Bootloader\TokenizerBootloader;

final class DirectoryBootloader extends Bootloader
{
    public function init(TokenizerBootloader $tokenizer): void
    {
        $tokenizer->addDirectory(\dirname(__DIR__, 2) . '/Fixtures/Bootloader');
    }
}
