<?php

declare(strict_types=1);

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Http;

use Psr\Http\Message\ResponseInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Files\FilesInterface;
use Spiral\Framework\ConsoleTest;
use Spiral\Http\EmitterInterface;
use Spiral\Http\SapiDispatcher;

class SapiTest extends ConsoleTest
{
    /** @var EmitterInterface */
    private $bufferEmitter;

    public function setUp(): void
    {
        $this->bufferEmitter = new class () implements EmitterInterface {
            public $response;

            public function emit(ResponseInterface $response): bool
            {
                $this->response = $response;
                return true;
            }
        };
        parent::setUp();
    }

    public function testCantServe(): void
    {
        $this->assertFalse($this->app->get(SapiDispatcher::class)->canServe());
    }

    public function testDispatch(): void
    {
        $e = $this->bufferEmitter;

        $app = $this->makeApp();

        $_SERVER['REQUEST_URI'] = '/index/dave';
        $app->get(SapiDispatcher::class)->serve($e);

        $this->assertSame('Hello, dave.', (string)$e->response->getBody());
    }

    public function testDispatchError(): void
    {
        $e = $this->bufferEmitter;

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(0, $files);

        $_SERVER['REQUEST_URI'] = '/error';
        $this->app->get(SapiDispatcher::class)->serve($e);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(1, $files);

        $this->assertContains('500', (string)$e->response->getBody());
    }

    public function testDispatchNativeError(): void
    {
        $e = $this->bufferEmitter;

        $app = $this->makeApp([
            'DEBUG' => true
        ]);

        $files = $app->get(FilesInterface::class)->getFiles(
            $app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(0, $files);

        $_SERVER['REQUEST_URI'] = '/error';
        $app->get(SapiDispatcher::class)->serve($e);

        $files = $app->get(FilesInterface::class)->getFiles(
            $app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(1, $files);

        $this->assertContains('undefined', (string)$e->response->getBody());
    }
}
