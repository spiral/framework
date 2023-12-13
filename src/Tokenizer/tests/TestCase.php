<?php

declare(strict_types=1);

namespace Spiral\Tests\Tokenizer;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class TestCase extends BaseTestCase
{
    protected function getTokenizer(array $config = []): Tokenizer
    {
        $config = new TokenizerConfig([
            'debug' => false,
            'directories' => [__DIR__],
            'exclude' => ['Excluded']
        ] + $config);

        return new Tokenizer($config);
    }
}
