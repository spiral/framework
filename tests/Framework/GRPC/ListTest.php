<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework\GRPC;

use Spiral\Files\Files;
use Spiral\Framework\ConsoleTest;

class ListTest extends ConsoleTest
{
    private $proto;

    public function setUp(): void
    {
        exec('protoc 2>&1', $out);
        if (strpos(join("\n", $out), '--php_out') === false) {
            $this->markTestSkipped('Protoc binary is missing');
            return;
        }

        parent::setUp();

        $fs = new Files();
        $this->proto = $fs->normalizePath($this->app->dir('app') . 'proto/service.proto');

        // protoc can't figure relative paths
        $this->proto = str_replace('Framework/../', '', $this->proto);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $fs = new Files();

        if ($fs->isDirectory($this->app->dir('app') . 'src/Service')) {
            $fs->deleteDirectory($this->app->dir('app') . 'src/Service');
        }

        if ($fs->isDirectory($this->app->dir('app') . 'src/GPBMetadata')) {
            $fs->deleteDirectory($this->app->dir('app') . 'src/GPBMetadata');
        }
    }

    public function testListEmpty(): void
    {
        $out = $this->runCommandDebug('grpc:services');

        $this->assertStringContainsString('No GRPC services', $out);
    }

    public function testListService(): void
    {
        $this->runCommandDebug('grpc:generate', [
            'proto' => $this->proto
        ]);

        file_put_contents($this->app->dir('app') . 'src/Service/EchoService.php', GenerateTest::SERVICE);

        $out = $this->runCommandDebug('grpc:services');

        $this->assertStringContainsString('service.Echo', $out);
        $this->assertStringContainsString('Spiral\App\Service\EchoService', $out);
    }
}
