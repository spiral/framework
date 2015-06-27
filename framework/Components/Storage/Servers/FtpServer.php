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
use Spiral\Components\Storage\StorageServer;

class FtpServer extends StorageServer
{
    /**
     * Configuration of FTP component, home directory, server options and etc.
     *
     * @var array
     */
    protected $options = array(
        'host'     => '',
        'port'     => 21,
        'timeout'  => 60,
        'login'    => '',
        'password' => '',
        'home'     => '/',
        'passive'  => true
    );

    /**
     * FTP connection resource.
     *
     * @var resource
     */
    protected $connection = null;

    /**
     * Every server represent one virtual storage which can be either local, remote or cloud based.
     * Every server should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param FileManager $file    File component.
     * @param array       $options Storage connection options.
     */
    public function __construct(FileManager $file, array $options)
    {
        parent::__construct($file, $options);

        if (!extension_loaded('ftp'))
        {
            throw new StorageException(
                "Unable to initialize ftp storage server, extension 'ftp' not found."
            );
        }

        $this->connect();
    }

    /**
     * Check if given object (name) exists in specified container. Method should never fail if file
     * not exists and will return bool in any condition.
     *
     * @param StorageContainer $container Container instance associated with specific server.
     * @param string           $name      Storage object name.
     * @return bool
     */
    public function isExists(StorageContainer $container, $name)
    {
        return ftp_size($this->connection, $this->getPath($container, $name)) != -1;
    }

    /**
     * Retrieve object size in bytes, should return false if object does not exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return int|bool
     */
    public function getSize(StorageContainer $container, $name)
    {
        if (($size = ftp_size($this->connection, $this->getPath($container, $name))) != -1)
        {
            return $size;
        }

        return false;
    }

    /**
     * Upload storage object using given filename or stream. Method can return false in case of failed
     * upload or thrown custom exception if needed.
     *
     * @param StorageContainer       $container Container instance.
     * @param string                 $name      Given storage object name.
     * @param string|StreamInterface $origin    Local filename or stream to use for creation.
     * @return bool
     */
    public function upload(StorageContainer $container, $name, $origin)
    {
        $location = $this->ensureLocation($container, $name);
        if (!ftp_put($this->connection, $location, $this->castFilename($origin), FTP_BINARY))
        {
            return false;
        }

        ftp_chmod(
            $this->connection,
            !empty($container->options['mode']) ? $container->options['mode'] : FileManager::RUNTIME,
            $location
        );

        return true;
    }

    /**
     * Allocate local filename for remote storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. File is in readonly
     * mode, and in some cases will be erased on shutdown.
     *
     * Method should return false or thrown an exception if local filename can not be allocated.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return string|bool
     */
    public function allocateFilename(StorageContainer $container, $name)
    {
        if (!$this->isExists($container, $name))
        {
            return false;
        }

        //File should be removed after processing
        $tempFilename = $this->file->tempFilename($this->file->extension($name));

        return ftp_get($this->connection, $tempFilename, $this->getPath($container, $name), FTP_BINARY)
            ? $tempFilename
            : false;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very similar
     * to localFilename, however in some cases it may store data content in memory.
     *
     * Method should return false or thrown an exception if stream can not be allocated.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return StreamInterface|null
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
     * Rename storage object without changing it's container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * Method should return false or thrown an exception if object can not be renamed.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $oldname   Storage object name.
     * @param string           $newname   New storage object name.
     * @return bool
     */
    public function rename(StorageContainer $container, $oldname, $newname)
    {
        if (!$this->isExists($container, $oldname))
        {
            return false;
        }

        $location = $this->ensureLocation($container, $newname);
        if (!ftp_rename($this->connection, $this->getPath($container, $oldname), $location))
        {
            return false;
        }

        ftp_chmod(
            $this->connection,
            !empty($container->options['mode']) ? $container->options['mode'] : FileManager::RUNTIME,
            $location
        );

        return true;
    }

    /**
     * Delete storage object from specified container. Method should not fail if object does not
     * exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     */
    public function delete(StorageContainer $container, $name)
    {
        if ($this->isExists($container, $name))
        {
            ftp_delete($this->connection, $this->getPath($container, $name));
        }
    }

    /**
     * Replace object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method should return false or thrown an exception if object can not be replaced.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Storage object name.
     * @return bool
     */
    public function replace(StorageContainer $container, StorageContainer $destination, $name)
    {
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
     * Ensure that FTP connection is up and can be used for file operations.
     *
     * @return bool
     * @throws StorageException
     */
    protected function connect()
    {
        if (!empty($this->connection))
        {
            return true;
        }

        $this->connection = ftp_connect(
            $this->options['host'],
            $this->options['port'],
            $this->options['timeout']
        );

        if (empty($this->connection))
        {
            throw new StorageException(
                "Unable to connect to remote FTP server '{$this->options['host']}'."
            );
        }

        if (!ftp_login($this->connection, $this->options['login'], $this->options['password']))
        {
            ftp_close($this->connection);

            throw new StorageException(
                "Unable to connect to remote FTP server '{$this->options['host']}'."
            );
        }

        if (!ftp_pasv($this->connection, $this->options['passive']))
        {
            ftp_close($this->connection);

            return false;
        }

        return true;
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
                    //todo: force mode
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
            $this->options['home'] . '/' . $container->options['folder'] . $name
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