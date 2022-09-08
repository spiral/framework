<?php

declare(strict_types=1);

namespace Spiral\Tests\Encrypter;

use PHPUnit\Framework\TestCase;
use Spiral\Encrypter\Config\EncrypterConfig;

class ConfigTest extends TestCase
{
    public function testKey(): void
    {
        $config = new EncrypterConfig([
            'key' => 'abc'
        ]);

        $this->assertSame('abc', $config->getKey());
    }
}
