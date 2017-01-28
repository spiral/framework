<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Tests\BaseTest;
use Spiral\Validation\ValidatorInterface;
use Zend\Diactoros\UploadedFile;

class FileCheckerTest extends BaseTest
{
    public function testExists()
    {
        $this->assertFail('a', [], [
            'a' => ['file:exists']
        ]);

        $this->assertFail('a', [
            'a' => null
        ], [
            'a' => ['file:exists']
        ]);

        $this->assertFail('a', [
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

    public function testExistsStream()
    {
        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 0);

        $this->assertValid([
            'a' => $uploaded
        ], [
            'a' => ['file:exists']
        ]);

        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 1);

        $this->assertFail('a', [
            'a' => $uploaded
        ], [
            'a' => ['file:exists']
        ]);
    }

    public function testUploaded()
    {
        $this->assertFail('a', [], [
            'a' => ['file:uploaded']
        ]);

        $this->assertFail('a', [
            'a' => null
        ], [
            'a' => ['file:uploaded']
        ]);

        $this->assertFail('a', [
            'a' => []
        ], [
            'a' => ['file:uploaded']
        ]);

        $this->assertFail('a', [
            'a' => __FILE__
        ], [
            'a' => ['file:uploaded']
        ]);
    }

    public function testUploadedSteam()
    {
        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 0);

        $this->assertValid([
            'a' => $uploaded
        ], [
            'a' => ['file:uploaded']
        ]);

        $uploaded = new UploadedFile(__FILE__, filesize(__FILE__), 1);

        $this->assertFail('a', [
            'a' => $uploaded
        ], [
            'a' => ['file:uploaded']
        ]);
    }

    public function testSize()
    {
        $this->assertFail('a', [], [
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
        $this->assertFail('a', [
            'a' => $tmpFile
        ], [
            'a' => [
                'file:exists',
                ['file:size', 1] //1Kb
            ]
        ]);
    }

    public function testSizeStream()
    {
        $this->assertFail('a', [], [
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
        $this->assertFail('a', [
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
        $this->assertFail('a', [
            'a' => new UploadedFile($tmpFile, filesize($tmpFile), 1)
        ], [
            'a' => [
                ['file:size', 1] //1Kb
            ]
        ]);
    }

    public function testExtension()
    {
        $this->assertFail('a', [], [
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

        $this->assertFail('a', [
            'a' => __FILE__
        ], [
            'a' => [
                'file:exists',
                ['file:extension', 'jpg']
            ]
        ]);
    }

    public function testExtensionUploaded()
    {
        $this->assertFail('a', [], [
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

        $this->assertFail('a', [
            'a' => $uploaded
        ], [
            'a' => [
                'file:exists',
                ['file:extension', 'jpg']
            ]
        ]);
    }

    protected function assertValid(array $data, array $rules)
    {
        $validator = $this->container->make(ValidatorInterface::class, ['rules' => $rules]);
        $validator->setData($data);

        $this->assertTrue($validator->isValid(), 'Validation FAILED');
    }

    protected function assertFail(string $error, array $data, array $rules)
    {
        $validator = $this->container->make(ValidatorInterface::class, ['rules' => $rules]);
        $validator->setData($data);

        $this->assertFalse($validator->isValid(), 'Validation PASSED');
        $this->assertArrayHasKey($error, $validator->getErrors());
    }
}