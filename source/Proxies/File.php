<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Proxies;

use Spiral\Components\Files\FileManager;
use Spiral\Core\Container;
use Spiral\Core\StaticProxy;

/**
 * @method static bool read($filename)
 * @method static bool write($filename, $data, $mode = null, $ensureDirectory = false, $append = false)
 * @method static bool append($filename, $data, $mode = null, $ensureDirectory = false)
 * @method static bool move($filename, $destination)
 * @method static bool copy($filename, $destination)
 * @method static bool delete($filename)
 * @method static bool touch($filename)
 * @method static bool exists($filename)
 * @method static int size($filename)
 * @method static bool extension($filename)
 * @method static bool md5($filename)
 * @method static int timeUpdated($filename)
 * @method static bool isUploaded($file, $local = true)
 * @method static void clearCache($filename = null)
 * @method static int getPermissions($filename, $clearCache = true)
 * @method static bool setPermissions($filename, $mode, $clearCache = true)
 * @method static array getFiles($directory, $extensions = null, &$result = [])
 * @method static string tempFilename($extension = '', $directory = null, $prefix = 'sp')
 * @method static string relativePath($location, $relativeTo = null)
 * @method static string normalizePath($path, $directory = false)
 * @method static bool ensureDirectory($directory, $mode = 511, $recursivePermissions = true)
 * @method static void removeFiles()
 * @method static FileManager make($parameters = [], Container $container = null)
 * @method static FileManager getInstance(Container $container = null)
 */
class File extends StaticProxy
{
    /**
     * Proxy can statically represent methods of one binded component, such component alias or class
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