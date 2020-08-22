<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Tests\Validation\BaseTest;
use Laminas\Diactoros\UploadedFile;

class FileTest extends BaseTest
{
    private $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = new Files();
        $this->container->bind(FilesInterface::class, $this->files);
    }

    public function testExists(): void
    {
        $this->assertNotValid('a', [], [
            'a' => ['file:exists']
        ]);

        $this->assertNotValid('a', [
            'a' => null
        ], [
            'a' => ['file:exists']
        ]);

        $this->assertNotValid('a', [
            'a' => []
        ], [
            'a' => ['file:exists']
        ]);

        $this->assertValid([
            'a' => __FILE__
        ], [
            'a' => ['file:exists']
        ]);
    }

    public function testFakeUpload(): void
    {
        $this->assertValid([
            'a' => ['tmp_name' => __FILE__]
        ], [
            'a' => ['file:exists']
        ]);

        $this->assertNotValid('a', [
            'a' => ['tmp_name' => __FILE__]
        ], [
            'a' => ['file:uploaded']
        ]);

        $this->assertValid([
            'a' => ['tmp_name' => __FILE__, 'uploaded' => true]
        ], [
            'a' => ['file:uploaded']
        ]);
    }

    public function testExistsStream(): void
    {
        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 0);

        $this->assertValid([
            'a' => $uploaded
        ], [
            'a' => ['file:exists']
        ]);

        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 1);

        $this->assertNotValid('a', [
            'a' => $uploaded
        ], [
            'a' => ['file:exists']
        ]);
    }

    public function testUploaded(): void
    {
        $this->assertNotValid('a', [], [
            'a' => ['file:uploaded']
        ]);

        $this->assertNotValid('a', [
            'a' => null
        ], [
            'a' => ['file:uploaded']
        ]);

        $this->assertNotValid('a', [
            'a' => []
        ], [
            'a' => ['file:uploaded']
        ]);

        $this->assertNotValid('a', [
            'a' => __FILE__
        ], [
            'a' => ['file:uploaded']
        ]);
    }

    public function testUploadedSteam(): void
    {
        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 0);

        $this->assertValid([
            'a' => $uploaded
        ], [
            'a' => ['file:uploaded']
        ]);

        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 1);

        $this->assertNotValid('a', [
            'a' => $uploaded
        ], [
            'a' => ['file:uploaded']
        ]);
    }

    public function testSize(): void
    {
        $this->assertNotValid('a', [], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);

        $tmpFile = $this->files->tempFilename();
        $this->files->write(
            $tmpFile,
            str_repeat('0', 1023)
        );

        clearstatcache();
        $this->assertValid([
            'a' => $tmpFile
        ], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);

        $tmpFile = $this->files->tempFilename();
        $this->files->write(
            $tmpFile,
            str_repeat('0', 1024)
        );

        clearstatcache();
        $this->assertValid([
            'a' => $tmpFile
        ], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);

        $tmpFile = $this->files->tempFilename();
        $this->files->write(
            $tmpFile,
            str_repeat('0', 1025)
        );

        clearstatcache();
        $this->assertNotValid('a', [
            'a' => $tmpFile
        ], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);
    }

    public function testSizeStream(): void
    {
        $this->assertNotValid('a', [], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);

        $tmpFile = $this->files->tempFilename();
        $this->files->write(
            $tmpFile,
            str_repeat('0', 1023)
        );

        clearstatcache();
        $this->assertValid([
            'a' => new UploadedFile($tmpFile, filesize($tmpFile), 0)
        ], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);

        $tmpFile = $this->files->tempFilename();
        $this->files->write(
            $tmpFile,
            str_repeat('0', 1024)
        );

        clearstatcache();
        $this->assertValid([
            'a' => new UploadedFile($tmpFile, filesize($tmpFile), 0)
        ], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);

        $tmpFile = $this->files->tempFilename();
        $this->files->write(
            $tmpFile,
            str_repeat('0', 1025)
        );

        clearstatcache();
        $this->assertNotValid('a', [
            'a' => new UploadedFile($tmpFile, filesize($tmpFile), 0)
        ], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);

        $tmpFile = $this->files->tempFilename();
        $this->files->write(
            $tmpFile,
            str_repeat('0', 1023)
        );

        clearstatcache();
        $this->assertNotValid('a', [
            'a' => new UploadedFile($tmpFile, filesize($tmpFile), 1)
        ], [
            'a' => [
                ['file:size', 1] //1Kb
            ]
        ]);
    }

    public function testExtension(): void
    {
        $this->assertNotValid('a', [], [
            'a' => [
                'file:exists',
                ['file:extension', 1] //1Kb
            ]
        ]);

        $this->assertValid([
            'a' => __FILE__
        ], [
            'a' => [
                'file:exists',
                ['file:extension', 'php']
            ]
        ]);

        $this->assertNotValid('a', [
            'a' => __FILE__
        ], [
            'a' => [
                'file:exists',
                ['file:extension', 'jpg']
            ]
        ]);
    }

    public function testExtensionUploaded(): void
    {
        $this->assertNotValid('a', [], [
            'a' => [
                'file:exists',
                ['file:extension', 1] //1Kb
            ]
        ]);

        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 0, 'file.php');

        $this->assertValid([
            'a' => $uploaded
        ], [
            'a' => [
                'file:exists',
                ['file:extension', 'php']
            ]
        ]);

        $this->assertNotValid('a', [
            'a' => $uploaded
        ], [
            'a' => [
                'file:exists',
                ['file:extension', 'jpg']
            ]
        ]);
    }
}
