<?php

declare(strict_types=1);

namespace Framework\Tokenizer\Config;

use Spiral\Testing\Attribute\Env;
use Spiral\Tests\Framework\BaseTestCase;
use Spiral\Tokenizer\Config\TokenizerConfig;

final class ConfigTest extends BaseTestCase
{
    #[Env('TOKENIZER_CACHE_TARGETS', 'false')]
    public function testCacheFromEnvDisabled(): void
    {
        $config = $this->getContainer()->get(TokenizerConfig::class);

        self::assertFalse($config->isCacheEnabled());
    }

    #[Env('TOKENIZER_CACHE_TARGETS', 'true')]
    public function testCacheFromEnvEnabled(): void
    {
        $config = $this->getContainer()->get(TokenizerConfig::class);

        self::assertTrue($config->isCacheEnabled());
    }
}
