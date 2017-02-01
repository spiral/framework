<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;

class FilesBagTest extends HttpTest
{
    public function testShortcut()
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

    public function testGetFilename()
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


    public function testGetFilenameMissing()
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