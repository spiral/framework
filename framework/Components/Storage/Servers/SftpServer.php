<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers;

use Psr\Http\Message\StreamInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Files\StreamWrapper;
use Spiral\Components\Http\Stream;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageException;
use Spiral\Components\Storage\StorageServer;

class SftpServer extends StorageServer
{
    /**
     * Configuration of FTP component, home directory, server options and etc.
     *
     * @var array
     */
    protected $options = array(
        'host' => '',
        'port' => 22,
        'home' => '/'
    );

    /**
     * SFTP connection resource.
     *
     * @var resource
     */
    protected $sftp = null;

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every adapter should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param FileManager $file    FileManager component.
     * @param array       $options Storage connection options.
     * @throws StorageException
     */
    public function __construct(FileManager $file, array $options)
    {
        parent::__construct($file, $options);

        if (!extension_loaded('ssh2'))
        {
            throw new StorageException(
                "Unable to initialize sftp storage server, extension 'ssh2' not found."
            );
        }

        $this->connect();
    }

    /**
     * Ensure that SSH connection is up and can be used for file operations.
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

        if (!$connection = ssh2_connect($this->options['host'], $this->options['port']))
        {
            throw new StorageException(
                "Unable to connect to remote SSH server '{$this->options['host']}'."
            );
        }

        //Authorization METHODS!
        ssh2_auth_password($connection, 'USERNAME', 'PASSWORD');

        $this->sftp = ssh2_sftp($connection);
    }

    /**
     * Check if given object (name) exists in specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool|array
     */
    public function isExists(StorageContainer $container, $name)
    {
        return file_exists($this->getUri($container, $name));
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
        if (!$this->isExists($container, $name))
        {
            return false;
        }

        return filesize($this->getUri($container, $name));
    }

    /**
     * Upload new storage object using given filename or stream.
     *
     * @param StorageContainer       $container Container instance.
     * @param string                 $name      Relative object name.
     * @param string|StreamInterface $origin    Local filename or stream to use for creation.
     * @return bool
     */
    public function upload(StorageContainer $container, $name, $origin)
    {
        if ($origin instanceof StreamInterface)
        {
            $expectedSize = $origin->getSize();
            $source = StreamWrapper::getResource($origin);
        }
        else
        {
            $expectedSize = filesize($origin);
            $source = fopen($origin, 'r');
        }

        //Remote file
        $this->ensureLocation($container, $name);
        $destination = fopen($this->getUri($container, $name), 'w');

        //We can check size here
        $size = stream_copy_to_stream($source, $destination);

        fclose($source);
        fclose($destination);

        return $expectedSize == $size;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very identical
     * to localFilename, however in some cases it may store data content in memory simplifying
     * development.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return StreamInterface|null
     */
    public function getStream(StorageContainer $container, $name)
    {
        return new Stream($this->getUri($container, $name));
    }

    /**
     * Remove storage object without changing it's own container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $oldname   Relative object name.
     * @param string           $newname   New object name.
     * @return bool
     */
    public function rename(StorageContainer $container, $oldname, $newname)
    {
        if (!$this->isExists($container, $oldname))
        {
            //return false;
        }

        $location = $this->ensureLocation($container, $newname);

        if (file_exists($this->getUri($container, $newname)))
        {
            //We have to clean location before renaming
            $this->delete($container, $newname);
        }

        //todo: exception
        dump(ssh2_sftp_rename($this->sftp, $this->getPath($container, $oldname), $location));

        if (!empty($container->options['mode']) && function_exists('ssh2_sftp_chmod'))
        {
            ssh2_sftp_chmod($this->sftp, $location, $container->options['mode']);
        }

        return true;
    }

    /**
     * Delete storage object from specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     */
    public function delete(StorageContainer $container, $name)
    {
        if ($this->isExists($container, $name))
        {
            ssh2_sftp_unlink($this->sftp, $this->getPath($container, $name));
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
        //Copying using local streams
        return $this->upload($destination, $name, $this->getStream($container, $name));
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
        if ($this->copy($container, $destination, $name))
        {
            $this->delete($container, $name);

            return true;
        }

        //exceptions?

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

        if (file_exists('ssh2.sftp://' . $this->sftp . $directory))
        {
            //Trying to change directory mode
            if (!empty($container->options['mode']) && function_exists('ssh2_sftp_chmod'))
            {
                ssh2_sftp_chmod($this->sftp, $directory, $container->options['mode'] | 0111);
            }

            return $this->getPath($container, $name);
        }

        //TODO: RECURSIVELLY?

        $directories = explode('/', substr($directory, strlen($this->options['home'])));

        $location = $this->options['home'];
        foreach ($directories as $directory)
        {
            if (!$directory)
            {
                continue;
            }

            $location .= '/' . $directory;

            if (!file_exists('ssh2.sftp://' . $this->sftp . $location))
            {
                if (!ssh2_sftp_mkdir($this->sftp, $location))
                {
                    //exception
                }

                if (!empty($container->options['mode']) && function_exists('ssh2_sftp_chmod'))
                {
                    ssh2_sftp_chmod($this->sftp, $location, $container->options['mode'] | 0111);
                }
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
            $this->options['home'] . '/' . $container->options['folder'] . '/' . $name
        );
    }

    /**
     * Get ssh2 specific uri which can be used in default php functions.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string
     */
    protected function getUri(StorageContainer $container, $name)
    {
        return 'ssh2.sftp://' . $this->sftp . $this->getPath($container, $name);
    }
}