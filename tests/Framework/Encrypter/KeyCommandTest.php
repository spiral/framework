<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\Encrypter;

use Spiral\Encrypter\EncrypterFactory;
use Spiral\Framework\ConsoleTest;

class KeyCommandTest extends ConsoleTest
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
        $key = $this->app->get(EncrypterFactory::class)->generateKey();

        $app = $this->makeApp([
            'ENCRYPTER_KEY' => $key
        ]);

        file_put_contents(__DIR__ . '/.env', $key);

        $out = $app->console()->run('encrypt:key', [
            '-m' => __DIR__ . '/.env'
        ]);
        $out = $out->getOutput()->fetch();

        $this->assertStringContainsString('key has been updated', $out);

        $body = file_get_contents(__DIR__ . '/.env');
        $this->assertStringContainsString($body, $out);

        unlink(__DIR__ . '/.env');
    }
}
