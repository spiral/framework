<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Storage\Bucket;
use Spiral\Storage\BucketInterface;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected const LOCAL_STORAGE_DIRECTORY = __DIR__ . '/storage';

    /**
     * @var BucketInterface
     */
    protected $local;

    /**
     * @var BucketInterface
     */
    protected $second;

    protected function setUp(): void
    {
        parent::setUp();

        $this->local = Bucket::fromAdapter(
            new LocalFilesystemAdapter(self::LOCAL_STORAGE_DIRECTORY),
        );

        $this->second = Bucket::fromAdapter(
            new LocalFilesystemAdapter(self::LOCAL_STORAGE_DIRECTORY . '/second'),
        );
    }

    protected function tearDown(): void
    {
        $this->cleanTempDirectory();

        parent::tearDown();
    }

    protected function cleanTempDirectory(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::LOCAL_STORAGE_DIRECTORY, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getFilename() === '.gitignore') {
                continue;
            }

            \error_clear_last();

            if ($file->isDir()) {
                @\rmdir($file->getPathname());
            } else {
                @\unlink($file->getPathname());
            }

            if ($error = \error_get_last()) {
                $prefix = 'An error occurred while clear temporary local storage directory: ';
                $this->addWarning($prefix . $error['message']);
            }
        }
    }
}
