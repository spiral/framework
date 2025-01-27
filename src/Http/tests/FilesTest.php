<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\Container;
use Spiral\Http\Request\InputManager;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;

class FilesTest extends TestCase
{
    private Container $container;
    private InputManager $input;

    public function testShortcut(): void
    {
        $request = new ServerRequest('GET', '');
        $request = $request->withUploadedFiles([
            'file' => new UploadedFile(
                \fopen(__FILE__, 'r'),
                \filesize(__FILE__),
                0,
                __FILE__,
            ),
        ]);

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertInstanceOf(UploadedFileInterface::class, $this->input->file('file'));
        self::assertNull($this->input->file('other'));
    }

    public function testGetFilename(): void
    {
        $request = new ServerRequest('GET', '');
        $request = $request->withUploadedFiles([
            'file' => new UploadedFile(
                \fopen(__FILE__, 'r'),
                \filesize(__FILE__),
                0,
                __FILE__,
            ),
        ]);

        $this->container->bind(ServerRequestInterface::class, $request);


        $filename = $this->input->files->getFilename('file');
        self::assertFileExists($filename);

        self::assertSame(\file_get_contents(__FILE__), \file_get_contents($filename));
    }

    public function testGetFilenameMissing(): void
    {
        $request = new ServerRequest('GET', '');
        $request = $request->withUploadedFiles([
            'file' => new UploadedFile(
                \fopen(__FILE__, 'r'),
                \filesize(__FILE__),
                0,
                __FILE__,
            ),
        ]);

        $this->container->bind(ServerRequestInterface::class, $request);

        $filename = $this->input->files->getFilename('file2');
        self::assertNull($filename);
    }

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }
}
