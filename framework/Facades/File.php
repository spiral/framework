<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Psr\Log\LoggerInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Core\Facade;

/**
 * @method static FileManager make(array $parameters = array())
 * @method static FileManager getInstance()
 *
 * @method static bool read(string $filename)
 * @method static bool write(string $filename, string $data, int $mode = null, bool $ensureDirectory = false)
 * @method static bool append(string $filename, string $data, int $mode = null, bool $ensureDirectory = false)
 * @method static bool replace(string $filename, string $destination)
 * @method static bool copy(string $filename, string $destination)
 * @method static bool remove(string $filename)
 * @method static bool exists(string $filename)
 * @method static int size(string $filename)
 * @method static bool extension(string $filename)
 * @method static bool md5(string $filename)
 * @method static int timeUpdated(string $filename)
 * @method static bool isUploaded(mixed $file, bool $local = true)
 * @method static clearCache(string $filename = null)
 * @method static int getPermissions(string $filename, bool $clearCache = true)
 * @method static bool setPermissions(string $filename, string $mode, bool $clearCache = true)
 * @method static array getFiles(string $directory, array $extensions = null, array $result = null)
 * @method static string tempFilename(string $extension = '', string $directory = null, string $prefix = 'sp')
 * @method static string relativePath(string $location, string $relativeTo = null)
 * @method static string normalizePath(string $path, bool $directory = false)
 * @method static bool ensureDirectory(string $directory, mixed $mode = 438, bool $recursivePermissions = true)
 * @method static FileManager blackspot(string $filename)
 * @method static removeFiles()
 * @method static setLogger(LoggerInterface $logger)
 * @method static LoggerInterface logger()
 * @method static string getAlias()
 */
class File extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'file';

    /**
     * Size constants for better size manipulations.
     */
    const KB = 1024;
    const MB = 1048576;
    const GB = 1073741824;

    /**
     * Default file permissions is 777 (directories 777), this files are fully writable and readable
     * by all application environments. Usually this files stored under application/data folder,
     * however they can be in some other public locations.
     */
    const RUNTIME = FileManager::RUNTIME;

    /**
     * Work files are files which create by or for framework, like controllers, configs and config
     * directories. This means that only CLI mode application can modify them. You should not create
     * work files from web application.
     */
    const READONLY = FileManager::READONLY;
}