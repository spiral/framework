<?php declare(strict_types=1);
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
use Spiral\Http\SapiDispatcher;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class SapiTest extends ConsoleTest
{
    public function testDispatch()
    {
        $e = new BufferEmitter();

        $app = $this->makeApp();

        $_SERVER['REQUEST_URI'] = '/index/dave';
        $app->get(SapiDispatcher::class)->serve($e);

        $this->assertSame('Hello, dave.', (string)$e->response->getBody());
    }

    public function testDispatchError()
    {
        $e = new BufferEmitter();

        $app = $this->makeApp();

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(0, $files);

        $_SERVER['REQUEST_URI'] = '/error';
        $app->get(SapiDispatcher::class)->serve($e);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(1, $files);

        $this->assertContains('500', (string)$e->response->getBody());
    }

    public function testDispatchNativeError()
    {
        $e = new BufferEmitter();

        $app = $this->makeApp([
            'DEBUG' => true
        ]);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(0, $files);

        $_SERVER['REQUEST_URI'] = '/error';
        $app->get(SapiDispatcher::class)->serve($e);

        $files = $this->app->get(FilesInterface::class)->getFiles(
            $this->app->get(DirectoriesInterface::class)->get('runtime') . '/snapshots/'
        );

        $this->assertCount(1, $files);

        $this->assertContains('undefined', (string)$e->response->getBody());
    }
}

class BufferEmitter implements EmitterInterface
{
    public $response;

    public function emit(ResponseInterface $response): bool
    {
        $this->response = $response;
        return true;
    }
}