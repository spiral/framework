<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Spiral\Core\Container;
use Spiral\Http\Request\InputManager;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\UploadedFile;

class FilesTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var InputManager
     */
    private $input;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }

    public function testShortcut(): void
    {
        $request = new ServerRequest();
        $request = $request->withUploadedFiles([
            'file' => new UploadedFile(
                fopen(__FILE__, 'r'),
                filesize(__FILE__),
                0,
                __FILE__
            )
        ]);

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertInstanceOf(UploadedFileInterface::class, $this->input->file('file'));
        $this->assertSame(null, $this->input->file('other'));
    }

    public function testGetFilename(): void
    {
        $request = new ServerRequest();
        $request = $request->withUploadedFiles([
            'file' => new UploadedFile(
                fopen(__FILE__, 'r'),
                filesize(__FILE__),
                0,
                __FILE__
            )
        ]);

        $this->container->bind(ServerRequestInterface::class, $request);


        $filename = $this->input->files->getFilename('file');
        $this->assertTrue(file_exists($filename));

        $this->assertSame(file_get_contents(__FILE__), file_get_contents($filename));
    }


    public function testGetFilenameMissing(): void
    {
        $request = new ServerRequest();
        $request = $request->withUploadedFiles([
            'file' => new UploadedFile(
                fopen(__FILE__, 'r'),
                filesize(__FILE__),
                0,
                __FILE__
            )
        ]);

        $this->container->bind(ServerRequestInterface::class, $request);

        $filename = $this->input->files->getFilename('file2');
        $this->assertNull($filename);
    }
}
