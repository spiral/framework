<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\GRPC;

use Spiral\Files\Files;
use Spiral\Tests\Framework\ConsoleTest;

class GenerateTest extends ConsoleTest
{
    public const SERVICE = '<?php
    namespace Spiral\App\Service;
    
    use Spiral\GRPC;
    use Spiral\App\Service\Sub\Message;

    class EchoService implements EchoInterface
    {
        public function Ping(GRPC\ContextInterface $ctx, Message $in): Message
        {
            return $in;
        }
    }
    ';
    private $proto;

    public function setUp(): void
    {
        exec('protoc 2>&1', $out);

        if (strpos(implode("\n", $out), '--php_out') === false) {
            $this->markTestSkipped('Protoc binary is missing');
        }

        parent::setUp();

        $fs = new Files();
        $this->proto = \realpath($fs->normalizePath($this->app->dir('app') . 'proto/service.proto'));
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

    public function testGenerateNotFound(): void
    {
        $out = $this->runCommandDebug('grpc:generate', [
            'proto' => 'notfound'
        ]);

        $this->assertStringContainsString('not found', $out);
    }

    public function testGenerateError(): void
    {
        $out = $this->runCommandDebug('grpc:generate', [
            'proto' => __FILE__
        ]);

        $this->assertStringContainsString('Error', $out);
    }

    public function testGenerate(): void
    {
        $this->runCommandDebug('grpc:generate', [
            'proto' => $this->proto
        ]);

        $this->assertFileExists($this->app->dir('app') . 'src/Service/EchoInterface.php');
        $this->assertFileExists($this->app->dir('app') . 'src/Service/Sub/Message.php');
        $this->assertFileExists($this->app->dir('app') . 'src/GPBMetadata/Service.php');
    }
}
