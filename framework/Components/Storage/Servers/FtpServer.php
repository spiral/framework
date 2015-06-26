<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */
namespace Spiral\Components\Storage\Servers;

use Psr\Http\Message\StreamInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Http\Stream;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageException;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageServer;

class FtpServer extends StorageServer
{
    /**
     * FTP connection resource.
     *
     * @var resource
     */
    protected $connection = null;

    /**
     * StorageManager component.
     *
     * @invisible
     * @var StorageManager
     */
    protected $storage = null;

    /**
     * Configuration of FTP component, home directory, server options and etc.
     *
     * @var array
     */
    protected $options = array(
        'server'   => '',
        'port'     => 21,
        'timeout'  => 60,
        'login'    => '',
        'password' => '',
        'home'     => '/',
        'passive'  => true
    );

    /**
     * File component.
     *
     * @var FileManager
     */
    protected $file = null;

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every adapter should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param array          $options Storage connection options.
     * @param StorageManager $storage StorageManager component.
     * @param FileManager    $file    FileManager component.
     * @throws StorageException
     */
    public function __construct(array $options, StorageManager $storage, FileManager $file)
    {
        $this->storage = $storage;
        $this->options = $options + $this->options;
        $this->file = $file;

        if (!function_exists('ftp_connect'))
        {
            throw new StorageException(
                "Unable to initialize ftp storage server, extension 'ftp' not found."
            );
        }
    }

    /**
     * Ensure that FTP connection is up and can be used for file operations.
     *
     * @return bool
     */
    protected function connect()
    {
        if (!empty($this->connection))
        {
            return true;
        }

        $this->connection = ftp_connect(
            $this->options['server'],
            $this->options['port'],
            $this->options['timeout']
        );

        if (empty($this->connection))
        {
            return false;
        }

        if (!ftp_login($this->connection, $this->options['login'], $this->options['password']))
        {
            ftp_close($this->connection);

            return false;
        }

        if (!ftp_pasv($this->connection, $this->options['passive']))
        {
            ftp_close($this->connection);

            return false;
        }

        return true;
    }

    /**
     * Check if given object (name) exists in specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool
     */
    public function isExists(StorageContainer $container, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        return ftp_size($this->connection, $this->getPath($container, $name)) != -1;
    }

    /**
     * Retrieve object size in bytes, should return 0 if object not exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return int
     */
    public function getSize(StorageContainer $container, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        return max(ftp_size($this->connection, $this->getPath($container, $name)), 0);
    }

    /**
     * Create new storage object using given filename. File will be replaced to new location and will
     * not available using old filename.
     *
     * @param StorageContainer       $container Container instance.
     * @param string                 $name      Relative object name.
     * @param string|StreamInterface $origin    Local filename or stream to use for creation.
     * @return bool
     */
    public function create(StorageContainer $container, $name, $origin)
    {
        if (!$this->connect())
        {
            return false;
        }

        if (!$this->file->exists($origin = $this->resolveFilename($origin)))
        {
            $origin = $this->file->tempFilename();
        }

        $location = $this->ensureLocation($container, $name);

        try
        {
            if (!ftp_put($this->connection, $location, $origin, FTP_BINARY))
            {
                return false;
            }

            if (!empty($container->options['mode']))
            {
                ftp_chmod($this->connection, $container->options['mode'], $location);
            }

            return true;
        }
        catch (\ErrorException $exception)
        {
        }

        return false;
    }

    /**
     * Allocate local filename for remove storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. All object stored in
     * temporary files should be registered in File::$removeFiles, to be removed after script ends to
     * clean used hard drive space.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string
     */
    public function allocateFilename(StorageContainer $container, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        if (!$this->isExists($container, $name))
        {
            return false;
        }

        /**
         * This method should be potentially updated to use getStream method as content provider.
         */

        //File should be removed after processing
        $this->file->blackspot($filename = $this->file->tempFilename($this->file->extension($name)));

        return ftp_get($this->connection, $filename, $this->getPath($container, $name), FTP_BINARY)
            ? $filename
            : false;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very identical
     * to localFilename, however in some cases it may store data content in memory simplifying
     * development.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return StreamInterface|bool
     */
    public function getStream(StorageContainer $container, $name)
    {
        if (!$filename = $this->allocateFilename($container, $name))
        {
            return false;
        }

        return new Stream($filename);
    }

    /**
     * Remove storage object without changing it's own container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @param string           $newName   New object name.
     * @return bool
     */
    public function rename(StorageContainer $container, $name, $newName)
    {
        if (!$this->connect())
        {
            return false;
        }

        if ($name == $newName)
        {
            return true;
        }

        if (!$this->isExists($container, $name))
        {
            return false;
        }

        $this->ensureLocation($container, $newName);
        $location = $this->getPath($container, $newName);

        try
        {
            if (!ftp_rename($this->connection, $this->getPath($container, $name), $location))
            {
                return false;
            }

            if (!empty($container->options['mode']))
            {
                ftp_chmod($this->connection, $container->options['mode'], $location);
            }

            return true;
        }
        catch (\ErrorException $exception)
        {
        }

        return false;
    }

    /**
     * Delete storage object from specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     */
    public function delete(StorageContainer $container, $name)
    {
        if (!$this->connect())
        {
            return;
        }

        if ($this->isExists($container, $name))
        {
            ftp_delete($this->connection, $this->getPath($container, $name));
        }
    }

    /**
     * Copy object to another internal (under same server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return bool
     */
    public function copy(StorageContainer $container, StorageContainer $destination, $name)
    {
        //Copying available only using buffer server
        if (!$filename = $this->allocateFilename($container, $name))
        {
            return false;
        }

        return $this->create($filename, $destination, $name);
    }

    /**
     * Move object to another internal (under same server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return bool
     */
    public function replace(StorageContainer $container, StorageContainer $destination, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        if ($container->options == $destination->options)
        {
            return true;
        }

        if (!$this->isExists($container, $name))
        {
            return false;
        }

        $location = $this->ensureLocation($container, $name);

        try
        {
            if (!ftp_rename($this->connection, $this->getPath($container, $name), $location))
            {
                return false;
            }

            if (!empty($container->options['mode']))
            {
                ftp_chmod($this->connection, $container->options['mode'], $location);
            }

            return true;
        }
        catch (\ErrorException $exception)
        {
        }

        return false;
    }

    /**
     * Ensure that target object directory exists and has right permissions.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool|string
     */
    protected function ensureLocation(StorageContainer $container, $name)
    {
        $directory = dirname($this->getPath($container, $name));

        try
        {
            if (ftp_chdir($this->connection, $directory))
            {
                if (!empty($container->options['mode']))
                {
                    ftp_chmod($this->connection, $container->options['mode'] | 0111, $directory);
                }

                return $this->getPath($container, $name);
            }
        }
        catch (\Exception $exception)
        {
            //Directory should be created
        }

        ftp_chdir($this->connection, $this->options['home']);

        $directories = explode('/', substr($directory, strlen($this->options['home'])));
        foreach ($directories as $directory)
        {
            if (!$directory)
            {
                continue;
            }

            try
            {
                ftp_chdir($this->connection, $directory);
            }
            catch (\Exception $exception)
            {
                ftp_mkdir($this->connection, $directory);

                if (!empty($container->options['mode']))
                {
                    ftp_chmod($this->connection, $container->options['mode'] | 0111, $directory);
                }

                ftp_chdir($this->connection, $directory);
            }
        }

        return $this->getPath($container, $name);
    }

    /**
     * Get full file location on server including homedir.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string
     */
    protected function getPath(StorageContainer $container, $name)
    {
        return $this->file->normalizePath(
            $this->options['home'] . $container->options['folder'] . $name
        );
    }

    /**
     * Destructing. FTP connection will be closed.
     */
    public function __destruct()
    {
        if (!empty($this->connection))
        {
            ftp_close($this->connection);
        }
    }
}