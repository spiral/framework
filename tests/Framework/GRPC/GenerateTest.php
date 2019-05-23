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

class GenerateTest extends ConsoleTest
{
    private $proto;

    public function setUp()
    {
        parent::setUp();

        $fs = new Files();
        $this->proto = $fs->normalizePath($this->app->dir('app') . 'proto/service.proto');

        // protoc can't figure relative paths
        $this->proto = str_replace('Framework/../', '', $this->proto);
    }

    public function tearDown()
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

    public function testGenerateNotFound()
    {
        $out = $this->runCommandDebug('grpc:generate', [
            'proto' => 'notfound'
        ]);

        $this->assertContains('not found', $out);
    }

    public function testGenerateError()
    {
        $out = $this->runCommandDebug('grpc:generate', [
            'proto' => __FILE__
        ]);

        $this->assertContains('Error', $out);
    }

    public function testGenerate()
    {
        $this->runCommandDebug('grpc:generate', [
            'proto' => $this->proto
        ]);

        $this->assertFileExists($this->app->dir('app') . 'src/Service/EchoInterface.php');
        $this->assertFileExists($this->app->dir('app') . 'src/Service/Message.php');
        $this->assertFileExists($this->app->dir('app') . 'src/GPBMetadata/Service.php');
    }
}