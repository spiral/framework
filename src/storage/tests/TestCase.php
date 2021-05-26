<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Storage\Storage;
use Spiral\Storage\StorageInterface;

class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected const LOCAL_STORAGE_DIRECTORY = __DIR__ . '/storage';

    /**
     * @var StorageInterface
     */
    protected $local;

    /**
     * @var StorageInterface
     */
    protected $second;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->local = Storage::fromAdapter(
            new LocalFilesystemAdapter(self::LOCAL_STORAGE_DIRECTORY)
        );

        $this->second = Storage::fromAdapter(
            new LocalFilesystemAdapter(self::LOCAL_STORAGE_DIRECTORY . '/second')
        );
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->cleanTempDirectory();

        parent::tearDown();
    }

    /**
     * @return void
     */
    protected function cleanTempDirectory(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::LOCAL_STORAGE_DIRECTORY, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $file */
        foreach($iterator as $file) {
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
