<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Encrypter;

use Spiral\Console\Console;
use Spiral\Encrypter\EncrypterFactory;
use Spiral\Tests\Framework\ConsoleTestCase;

final class KeyCommandTest extends ConsoleTestCase
{
    public function testKey(): void
    {
        $key = $this->runCommand('encrypt:key');
        $this->assertNotEmpty($key);
    }

    public function testMountFileNotFound(): void
    {
        $out = $this->runCommand('encrypt:key', [
            '-m' => __DIR__ . '/.env'
        ]);

        $this->assertStringContainsString('Unable to find', $out);
    }

    public function testReplace(): void
    {
        file_put_contents(__DIR__ . '/.env', '{encrypt-key}');

        $out = $this->runCommand('encrypt:key', [
            '-m' => __DIR__ . '/.env'
        ]);

        $this->assertStringContainsString('key has been updated', $out);

        $body = file_get_contents(__DIR__ . '/.env');
        $this->assertStringContainsString($body, $out);

        unlink(__DIR__ . '/.env');
    }

    public function testReplaceCurrent(): void
    {
        $key = $this->getContainer()->get(EncrypterFactory::class)->generateKey();

        $app = $this->makeApp([
            'ENCRYPTER_KEY' => $key
        ]);

        file_put_contents(__DIR__ . '/.env', $key);

        $out = $app->getContainer()->get(Console::class)->run('encrypt:key', [
            '-m' => __DIR__ . '/.env'
        ]);

        $out = $out->getOutput()->fetch();

        $this->assertStringContainsString('key has been updated', $out);

        $body = file_get_contents(__DIR__ . '/.env');
        $this->assertStringContainsString($body, $out);

        unlink(__DIR__ . '/.env');
    }
}
