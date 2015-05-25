<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers\Rackspace;

use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Storage\ServerInterface;
use Spiral\Components\Storage\Servers\Traits\MimetypeTrait;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageException;
use Spiral\Components\Storage\StorageManager;

class RackspaceCloudServer implements ServerInterface
{
    /**
     * Common storage server functionality.
     */
    use MimetypeTrait;

    /**
     * Server configuration, connection options, auth keys and certificates.
     *
     * @var array
     */
    protected $options = array(
        'server'      => 'auth.api.rackspacecloud.com/v1.0',
        'authServer'  => 'https://identity.api.rackspacecloud.com/v2.0/tokens',
        'secured'     => true,
        'certificate' => '',
        'username'    => '',
        'accessKey'   => '',
        'cache'       => true
    );

    /**
     * Current connection credentials. If cache options set to true in server options credentials will
     * be stored between sessions. Authentication tokens are typically valid for 24 hours. Applications
     * should be designed to re-authenticate after receiving a 401 (Unauthorized) response from a
     * service endpoint.
     *
     * @var array
     */
    private $credentials = array();

    /**
     * All fetched rackspace regions, some operations can be performed only inside one region.
     *
     * @var array
     */
    protected $regions = array();

    /**
     * Cache component to remember connection.
     *
     * @var CacheManager
     */
    protected $cache = null;

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every adapter should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param array          $options Storage connection options.
     * @param StorageManager $storage StorageManager component.
     * @param FileManager    $file    FileManager component.
     * @param CacheManager   $cache   CacheManager to remember connection credentials.
     */
    public function __construct(
        array $options,
        StorageManager $storage,
        FileManager $file,
        CacheManager $cache = null
    )
    {
        $this->options = $options + $this->options;
        $this->storage = $storage;
        $this->file = $file;

        $this->cache = $cache;

        if ($this->options['cache'])
        {
            $this->credentials = $this->cache->get(
                $this->options['username'] . '@rackspace-credentials'
            );

            if (empty($this->credentials))
            {
                $this->credentials = array();
            }

            $this->regions = $this->cache->get(
                $this->options['username'] . '@rackspace-regions'
            );

            if (empty($this->regions))
            {
                $this->regions = array();
            }
        }
    }

    /**
     * Connect rackspace to cloud, fetch credentials (Authentication tokens) and regions. Authentication
     * tokens are typically valid for 24 hours. Applications should be designed to re-authenticate after
     * receiving a 401 (Unauthorized) response from a service endpoint.
     *
     * @param bool $reset Request new credentials ignoring currently existed values.
     * @return bool
     */
    protected function connect($reset = false)
    {
        if (!$reset && $this->credentials)
        {
            return true;
        }

        $query = RackspaceQuery::make(array(
            'options' => $this->options,
            'URL'     => $this->options['authServer'],
            'method'  => 'POST'
        ));

        $result = $query->setRawPOST(json_encode(array(
            'auth' => array('RAX-KSKEY:apiKeyCredentials' => array(
                'username' => $this->options['username'],
                'apiKey'   => $this->options['accessKey']
            )))))
            ->setHeader('Content-Type', 'application/json')
            ->run();

        $content = json_decode($result['content'], 1);
        if ($result['status'] == 200 && is_array($content))
        {
            foreach ($content['access']['serviceCatalog'] as $location)
            {
                if ($location['name'] == 'cloudFiles')
                {
                    foreach ($location['endpoints'] as $server)
                    {
                        $this->regions[$server['region']] = $server['publicURL'];
                    }
                }
            }

            if (!isset($content['access']['token']['id']))
            {
                return false;
            }

            $this->credentials['x-auth-token'] = $content['access']['token']['id'];

            if ($this->options['cache'])
            {
                $this->cache->set(
                    $this->options['username'] . '@rackspace-credentials',
                    $this->credentials,
                    86400
                );

                $this->cache->set(
                    $this->options['username'] . '@rackspace-regions',
                    $this->regions,
                    86400
                );
            }

            return true;
        }

        return false;
    }

    /**
     * Rackspace query helper used to perform requests to S3 storage servers.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @param string           $method    HTTP method.
     * @return RackspaceQuery
     */
    protected function query(StorageContainer $container, $name, $method = 'HEAD')
    {
        $url = $this->regionURL($container->options['region']);
        $url .= '/' . $container->options['container'] . '/' . rawurlencode($name);

        $query = new RackspaceQuery($this->options, $url, $method);

        return $query
            ->setHeader('X-Auth-Token', $this->credentials['x-auth-token'])
            ->setHeader('Date', gmdate('D, d M Y H:i:s T'));
    }

    /**
     * Find url for given region id.
     *
     * @param string $region Region ID.
     * @return mixed
     * @throws StorageException
     */
    protected function regionURL($region)
    {
        if (!isset($this->regions[$region]))
        {
            throw new StorageException("'{$region}' region is not supported by Rackspace.");
        }

        return $this->regions[$region];
    }

    /**
     * Check if given object (name) exists in specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @param array            $headers   Headers associated with file.
     * @return bool
     */
    public function exists(StorageContainer $container, $name, &$headers = null)
    {
        if (!$this->connect())
        {
            return false;
        }

        $headers = $this->query($container, $name)->run();

        if ($headers['status'] == 401)
        {
            return $this->connect(true) && $this->exists($container, $name, $headers);
        }

        return $headers['status'] == 200;
    }

    /**
     * Retrieve object size in bytes, should return 0 if object not exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return int
     */
    public function filesize(StorageContainer $container, $name)
    {
        if (!$this->exists($container, $name, $headers))
        {
            return 0;
        }

        return (int)$headers['content-length'];
    }

    /**
     * Create new storage object using given filename. File will be replaced to new location and will
     * not available using old filename.
     *
     * @param string           $filename  Local filename to use for creation.
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool
     */
    public function create($filename, StorageContainer $container, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        if (!$this->file->exists($filename))
        {
            $filename = $this->file->tempFilename();
        }

        if (($mimetype = $this->getMimetype($filename)) == $this->mimetypes['default'])
        {
            $mimetype = $this->getMimetype($name);
        }

        $result = $this->query($container, $name, 'PUT')
            ->setHeader('Etag', md5_file($filename))
            ->setHeader('Content-Type', $mimetype)
            ->setHeader('Expect', '')
            ->filename($filename)
            ->run();

        if ($result['status'] == 401)
        {
            return $this->connect(true) && $this->create($filename, $container, $name);
        }

        return $result['status'] == 200 || $result['status'] == 201;
    }

    /**
     * Allocate local filename for remove storage object, if container represent remote location,
     * adapter should download file to temporary file and return it's filename. All object stored in
     * temporary files should be registered in File::$removeFiles, to be removed after script ends
     * to clean used hard drive space.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return string
     */
    public function localFilename(StorageContainer $container, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        //File should be removed after processing
        $this->file->blackspot($filename = $this->file->tempFilename($this->file->extension($name)));
        $result = $this->query($container, $name, 'GET')->filename($filename)->run();

        if ($result['status'] == 401)
        {
            return $this->connect(true) && $this->localFilename($container, $name);
        }

        return $result['status'] == 200 ? $filename : false;
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

        if ($newName == $name)
        {
            return true;
        }

        $result = $this->query($container, $name, 'COPY')
            ->setHeader(
                'Destination',
                '/' . $container->options['container'] . '/' . rawurlencode($newName)
            )->run();

        if ($result['status'] == 401)
        {
            return $this->connect(true) && $this->rename($container, $name, $name);
        }

        if ($result['status'] == 200 || $result['status'] == 201)
        {
            return $this->delete($container, $name);
        }

        return false;
    }

    /**
     * Delete storage object from specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool
     */
    public function delete(StorageContainer $container, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        $result = $this->query($container, $name, 'DELETE')->run();

        if ($result['status'] == 401)
        {
            $this->connect(true) && $this->delete($container, $name);
        }

        return true;
    }

    /**
     * Copy object to another internal (under save server) container, this operation should may not
     * require file download and can be performed remotely.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Relative object name.
     * @return bool
     */
    public function copy(StorageContainer $container, StorageContainer $destination, $name)
    {
        if (!$this->connect())
        {
            return false;
        }

        if ($container->options['container'] == $destination->options['container'])
        {
            return true;
        }

        if (strcasecmp($container->options['region'], $destination->options['region']) !== 0)
        {
            $this->storage->logger()->warning(
                "Copying between regions are not allowed by Rackspace and performed using local buffer."
            );

            return $this->create($this->localFilename($container, $name), $destination, $name);
        }

        $result = $this->query($destination, $name, 'PUT')
            ->setHeader('X-Copy-From', '/' . $container->options['container'] . '/' . rawurlencode($name))
            ->setHeader('Content-Length', 0)
            ->run();

        if ($result['status'] == 401)
        {
            return $this->connect(true) && $this->connect($container, $destination, $name);
        }

        return $result['status'] == 201 || $result['status'] == 200 || $result['status'] == 204;
    }

    /**
     * Move object to another internal (under save server) container, this operation should may not
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

        if ($container->options['container'] == $destination->options['container'])
        {
            return true;
        }

        if (strcasecmp($container->options['region'], $destination->options['region']) !== 0)
        {
            $this->storage->logger()->warning(
                "Moving between regions are not allowed by Rackspace and performed using local buffer."
            );

            $this->create($this->localFilename($container, $name), $destination, $name);

            return $this->delete($container, $name);
        }

        $result = $this->query($container, $name, 'COPY')
            ->setHeader('Destination', '/' . $destination->options['container'] . '/' . rawurlencode($name))
            ->run();

        if ($result['status'] == 401)
        {
            return $this->connect(true) && $this->replace($container, $destination, $name);
        }

        if ($result['status'] == 201)
        {
            return $this->delete($container, $name);
        }

        return false;
    }
}