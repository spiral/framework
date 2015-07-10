<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Files;

use Spiral\Core\Component;

class FileManager extends Component
{
    /**
     * Will provide us helper method getInstance().
     */
    use Component\SingletonTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Size constants for better size manipulations.
     */
    const KB = 1024;
    const MB = 1048576;
    const GB = 1073741824;

    /**
     * Default file permissions is 777, this files are fully writable and readable
     * by all application environments. Usually this files stored under application/data folder,
     * however they can be in some other public locations.
     */
    const RUNTIME = 0777;

    /**
     * Work files are files which created by or for framework, like controllers, configs and config
     * directories. This means that only CLI mode application can modify them. You should not create
     * work files from web application.
     */
    const READONLY = 0666;

    /**
     * Files marked to be removed after application ends, can be temporary files or other data.
     *
     * @var array
     */
    protected $removeFiles = [];

    /**
     * Initiating file component, mapping remove files method.
     */
    public function __construct()
    {
        register_shutdown_function([$this, 'removeFiles']);
    }

    /**
     * A simple alias for file_get_contents, no real reason for using it, only to keep code clean.
     *
     * @param string $filename
     * @return string
     */
    public function read($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * Write file to specified directory, and update file permissions if necessary. Function can
     * additionally ensure that target file directory exists.
     *
     * @param string $filename
     * @param string $data            String data to write, can contain binary data.
     * @param int    $mode            Use File::RUNTIME for 777
     * @param bool   $ensureDirectory If true, helper will ensure that destination directory exists
     *                                and have right permissions.
     * @param bool   $append          Will append file content.
     * @return bool
     */
    public function write($filename, $data, $mode = null, $ensureDirectory = false, $append = false)
    {
        $ensureDirectory && $this->ensureDirectory(dirname($filename), $mode);
        if (!empty($mode) && $this->exists($filename))
        {
            //Forcing mode for existed file
            $this->setPermissions($filename, $mode);
        }

        $result = (file_put_contents(
                $filename,
                $data,
                $append ? FILE_APPEND | LOCK_EX : LOCK_EX
            ) !== false);

        if ($result && !empty($mode))
        {
            //Forcing mode after file creation
            $this->setPermissions($filename, $mode);
        }

        return $result;
    }

    /**
     * Append file, this method is alias for Files->write() with forced append flag.
     *
     * @param string $filename
     * @param string $data            String data to write, can contain binary data.
     * @param int    $mode            Use File::RUNTIME for 666
     * @param bool   $ensureDirectory If true, helper will ensure that destination directory exists
     *                                and have right
     *                                permissions.
     * @return bool
     */
    public function append($filename, $data, $mode = null, $ensureDirectory = false)
    {
        return $this->write($filename, $data, $mode, $ensureDirectory, true);
    }

    /**
     * Move a file to a new location.
     *
     * @see rename()
     * @param string $filename
     * @param string $destination
     * @return bool
     */
    public function move($filename, $destination)
    {
        return rename($filename, $destination);
    }

    /**
     * Copy file to new location.
     *
     * @see copy()
     * @param string $filename
     * @param string $destination
     * @return bool
     */
    public function copy($filename, $destination)
    {
        return copy($filename, $destination);
    }

    /**
     * Will try to remove file. No exception will be thrown if file no exists.
     *
     * @see delete()
     * @param string $filename
     * @return bool
     */
    public function delete($filename)
    {
        if ($this->exists($filename))
        {
            return unlink($filename);
        }

        return false;
    }

    /**
     * Sets access and modification time of file. File will be automatically created on touch.
     *
     * @param string $filename
     * @return bool
     */
    public function touch($filename)
    {
        return touch($filename);
    }

    /**
     * Check if file exists.
     *
     * @param string $filename
     * @return bool
     */
    public function exists($filename)
    {
        return file_exists($filename);
    }

    /**
     * Get filesize in bytes if file exists.
     *
     * @param string $filename
     * @return int
     */
    public function size($filename)
    {
        if (!$this->exists($filename))
        {
            return 0;
        }

        return filesize($filename);
    }

    /**
     * Will chunk extension from file name.
     *
     * @param string $filename
     * @return bool
     */
    public function extension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Get file MD5 hash.
     *
     * @param string $filename
     * @return bool
     */
    public function md5($filename)
    {
        return md5_file($filename);
    }

    /**
     * This function returns the time when the data blocks of a file were being written to, that is,
     * the time when the content of the file was changed.
     *
     * @link http://php.net/manual/en/function.filemtime.php
     * @param string $filename
     * @return int
     */
    public function timeUpdated($filename)
    {
        if (!$this->exists($filename))
        {
            return 0;
        }

        return filemtime($filename);
    }

    /**
     * Check if provided file were uploaded, is_uploaded_file() function will be used to check it.
     * Methods can accept both as filename, as file upload array. In second case, addition flag "local"
     * will be checked if allowed to pass validation. This flag widely used if FileUpload class.
     *
     * @param mixed $file  Filename or file array.
     * @param bool  $local True to pass file arrays generated locally.
     * @return bool
     */
    public function isUploaded($file, $local = true)
    {
        if (is_string($file))
        {
            return is_uploaded_file($file);
        }

        return is_uploaded_file($file['tmp_name']) || ($local && !empty($file['local']));
    }

    /**
     * When you use stat(), lstat(), or any of the other functions listed in the affected functions
     * list (below), PHP caches the information those functions return in order to provide faster
     * performance. However, in certain cases, you may want to clear the cached information. For
     * instance, if the same file is being checked multiple times within a single script, and that
     * file is in danger of being removed or changed during that script's operation, you may elect
     * to clear the status cache. In these cases, you can use the clearstatcache() function to clear
     * the information that PHP caches about a file.
     *
     * @link http://php.net/manual/en/function.clearstatcache.php
     * @param string $filename All files by default.
     */
    public function clearCache($filename = null)
    {
        $filename ? clearstatcache(true, realpath($filename)) : clearstatcache();
    }

    /**
     * File permissions with 777 binary mask.
     *
     * @param string $filename
     * @param bool   $clearCache Rest php file cache as it can hold wrong value.
     * @return int
     */
    public function getPermissions($filename, $clearCache = true)
    {
        $clearCache && $this->clearCache($filename);

        return fileperms($filename) & 0777;
    }

    /**
     * Change file permission mode.
     *
     * @param string $filename
     * @param string $mode       Use File::RUNTIME for 666
     * @param bool   $clearCache Rest php file cache as it can hold wrong value.
     * @return bool
     */
    public function setPermissions($filename, $mode, $clearCache = true)
    {
        $clearCache && $this->clearCache($filename);

        if (is_dir($filename))
        {
            $mode |= 0111;
        }

        return $this->getPermissions($filename) == $mode || chmod($filename, $mode);
    }


    /**
     * Will read all available files from specified directory, including nested directories and files.
     * Will not include empty directories to list. You can specify to exclude some files by their
     * extension, for example to find only php files.
     *
     * @param string     $directory  Root directory to index.
     * @param null|array $extensions Array of extensions to include to indexation. Any other extension
     *                               will be ignored.
     * @param array      $result
     * @return array
     */
    public function getFiles($directory, $extensions = null, &$result = [])
    {
        if (is_string($extensions))
        {
            $extensions = [$extensions];
        }

        $directory = $this->normalizePath($directory, true);

        $glob = glob($directory . '*');
        foreach ($glob as $item)
        {
            if (is_dir($item))
            {
                self::getFiles($item . '/', $extensions, $result);
                continue;
            }

            if (!empty($extensions) && !in_array($this->extension($item), $extensions))
            {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Will create temporary unique file with desired extension, by default no extension will be used
     * and default tempnam() function will be used. You can specify temp directory where file should
     * be created, by default /tmp will be used. Make sure this directory is available for writing
     * for php process.
     *
     * File prefix can be used to identify files created under multiple applications, make sure that
     * prefix should follow same rules as for tempnam() function.
     *
     * @param string $extension Desired file extension, empty (no extension) by default.
     * @param string $directory Directory where file should be created, false (system temp dir) by
     *                          default.
     * @param string $prefix    File prefix, "sp" by default.
     * @return string
     */
    public function tempFilename($extension = '', $directory = null, $prefix = 'sp')
    {
        if (!empty($directory))
        {
            $directory = sys_get_temp_dir();
        }

        $tempFilename = tempnam($directory, $prefix);
        if ($extension)
        {
            //We probably should find more optimal way of doing that
            rename($tempFilename, $tempFilename = $tempFilename . '.' . $extension);
            $this->removeFiles[] = $tempFilename;
        }

        return $tempFilename;
    }

    /**
     * Getting relative location based on absolute path.
     *
     * @link http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
     * @param string $location   Original file or directory location.
     * @param string $relativeTo Path will be converted to be relative to this directory. By default
     *                           application root directory will be used.
     * @return string
     */
    public function relativePath($location, $relativeTo = null)
    {
        $relativeTo = $relativeTo ?: directory('root');

        //Always directory
        $relativeTo = $this->normalizePath($relativeTo) . '/';
        $location = $this->normalizePath($location);

        if (is_dir($location))
        {
            $location = rtrim($location, '/') . '/';
        }

        $relativeTo = explode('/', $relativeTo);
        $location = explode('/', $location);

        $relPath = $location;
        foreach ($relativeTo as $depth => $directory)
        {
            //Find first non-matching directory
            if ($directory === $location[$depth])
            {
                //Ignore this directory
                array_shift($relPath);
            }
            else
            {
                //Get number of remaining dirs to $from
                $remaining = count($relativeTo) - $depth;
                if ($remaining > 1)
                {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                }
                else
                {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }

        return implode('/', $relPath);
    }

    /**
     * Will normalize directory of file path to unify it (using UNIX directory separator /), windows
     * symbol "\" requires escaping and not very "visual" for identifying files. This function will
     * always remove end slash for path (even for directories).
     *
     * @param string $path      File or directory path.
     * @param bool   $directory Force end slash for directory path.
     * @return string
     */
    public function normalizePath($path, $directory = false)
    {
        $path = str_replace('\\', '/', $path);

        //Removing all double slashes
        return rtrim(str_replace('//', '/', $path), '/') . ($directory ? '/' : '');
    }

    /**
     * Make sure directory exists and has right permissions, works recursively.
     *
     * @param string $directory            Target directory.
     * @param mixed  $mode                 Use File::RUNTIME for 777
     * @param bool   $recursivePermissions Use this flag to apply permissions to all *created*
     *                                     directories. This flag used by system to ensure that all
     *                                     files and folders in runtime directory has right permissions,
     *                                     and by local storage server due it can create sub folders.
     *                                     This is slower by safer than using umask().
     * @return bool
     */
    public function ensureDirectory($directory, $mode = self::RUNTIME, $recursivePermissions = true)
    {
        $mode = $mode | 0111;
        if (is_dir($directory))
        {
            return $this->setPermissions($directory, $mode);
        }

        if ($recursivePermissions)
        {
            $directories = [basename($directory)];
            $baseDirectory = $directory;

            while (!is_dir($baseDirectory = dirname($baseDirectory)))
            {
                $directories[] = basename($baseDirectory);
            }

            foreach (array_reverse($directories) as $directory)
            {
                if (!mkdir($baseDirectory = $baseDirectory . '/' . $directory))
                {
                    return false;
                }

                chmod($baseDirectory, $mode);
            }

            return true;
        }

        return mkdir($directory, $mode, true);
    }

    /**
     * Clean all registered temporary files. This method can be called manually in any script, or
     * automatically while file
     * component destruction.
     */
    public function removeFiles()
    {
        foreach ($this->removeFiles as $filename)
        {
            $this->delete($filename);
        }
    }

    /**
     * All registered temporary files will be automatically removed when component destructed.
     */
    public function __destruct()
    {
        $this->removeFiles();
    }
}