<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Spiral\Components\Cache\CacheManager;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Http\Uri;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageException;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageServer;

class RackspaceServer extends StorageServer
{
    /**
     * Default cache lifetime is 24 hours.
     */
    const CACHE_LIFETIME = 86400;

    /**
     * Server configuration, connection options, auth keys and certificates.
     *
     * @var array
     */
    protected $options = array(
        'server'     => 'https://auth.api.rackspacecloud.com/v1.0',
        'authServer' => 'https://identity.api.rackspacecloud.com/v2.0/tokens',
        'username'   => '',
        'apiKey'     => '',
        'cache'      => true
    );

    /**
     * Cache component to remember connection.
     *
     * @invisible
     * @var CacheManager
     */
    protected $cache = null;

    /**
     * Guzzle client.
     *
     * @var Client
     */
    protected $client = null;

    /**
     * Rackspace authentication token.
     *
     * @var string
     */
    protected $authToken = array();

    /**
     * All fetched rackspace regions, some operations can be performed only inside one region.
     *
     * @var array
     */
    protected $regions = array();

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every server should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param FileManager  $file    File component.
     * @param array        $options Storage connection options.
     * @param CacheManager $cache   CacheManager to remember connection credentials across sessions.
     */
    public function __construct(FileManager $file, array $options, CacheManager $cache = null)
    {
        parent::__construct($file, $options);
        $this->cache = $cache;

        if (!empty($this->options['cache']))
        {
            $this->authToken = $this->cache->get($this->options['username'] . '@rackspace-token');
            $this->regions = $this->cache->get($this->options['username'] . '@rackspace-regions');
        }

        //Some options can be passed directly for guzzle client
        $this->client = new Client($this->options);

        $this->connect();
    }

    /**
     * Check if given object (name) exists in specified container.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return bool|ResponseInterface
     */
    public function isExists(StorageContainer $container, $name)
    {
        try
        {
            $response = $this->client->send($this->buildRequest('HEAD', $container, $name));
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 404)
            {
                return false;
            }

            if ($exception->getCode() == 401)
            {
                $this->reconnect();

                return $this->isExists($container, $name);
            }

            throw $exception;
        }

        if ($response->getStatusCode() !== 200)
        {
            return false;
        }

        return $response;
    }

    /**
     * Retrieve object size in bytes, should return 0 if object not exists.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @return int|bool
     */
    public function getSize(StorageContainer $container, $name)
    {
        if (empty($response = $this->isExists($container, $name)))
        {
            return false;
        }

        return (int)$response->getHeaderLine('Content-Length');
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
        if (empty($mimetype = \GuzzleHttp\Psr7\mimetype_from_filename($name)))
        {
            $mimetype = self::DEFAULT_MIMETYPE;
        }

        try
        {
            $this->client->send($this->buildRequest('PUT', $container, $name, array(
                'Content-Type' => $mimetype,
                'Etag'         => md5_file($this->resolveFilename($origin))
            ))->withBody($this->resolveStream($origin)));
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 401)
            {
                $this->reconnect();

                return $this->upload($container, $name, $origin);
            }
        }

        return true;
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
        try
        {
            $response = $this->client->send(
                $this->buildRequest('GET', $container, $name)
            );
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 401)
            {
                $this->reconnect();

                return $this->getStream($container, $name);
            }

            //Reasonable?
            if ($exception->getCode() != 404)
            {
                throw $exception;
            }

            return null;
        }

        return $response->getBody();
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
        try
        {
            $this->client->send(
                $this->buildRequest(
                    'PUT',
                    $container,
                    $newname,
                    array(
                        'X-Copy-From'    => '/' . $container->options['container'] . '/' . rawurlencode($oldname),
                        'Content-Length' => 0
                    )
                )
            );
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 401)
            {
                $this->reconnect();

                return $this->rename($container, $oldname, $newname);
            }

            throw $exception;
        }

        //Deleting old file
        $this->delete($container, $oldname);

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
        try
        {
            $this->client->send($this->buildRequest('DELETE', $container, $name));
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 401)
            {
                $this->reconnect();

                return $this->delete($container, $name);
            }

            if ($exception->getCode() != 404)
            {
                throw $exception;
            }
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
        if ($container->options['region'] != $destination->options['region'])
        {
            StorageManager::logger()->warning(
                "Copying between regions are not allowed by Rackspace and performed using local buffer."
            );

            return $this->upload($destination, $name, $this->getStream($container, $name));
        }

        try
        {
            $this->client->send(
                $this->buildRequest(
                    'PUT',
                    $destination,
                    $name,
                    array(
                        'X-Copy-From'    => '/' . $container->options['container'] . '/' . rawurlencode($name),
                        'Content-Length' => 0
                    )
                )
            );
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 401)
            {
                $this->reconnect();

                return $this->connect($container, $destination, $name);
            }

            throw $exception;
        }

        return true;
    }

    /**
     * Connect rackspace to cloud, fetch credentials (Authentication tokens) and regions. Authentication
     * tokens are typically valid for 24 hours.
     */
    protected function connect()
    {
        if (!empty($this->authToken))
        {
            //Already got credentials from cache
            return true;
        }

        //Credentials request
        $request = new Request(
            'POST',
            $this->options['authServer'],
            array(
                'Content-Type' => 'application/json'
            ),
            json_encode(
                array(
                    'auth' => array(
                        'RAX-KSKEY:apiKeyCredentials' => array(
                            'username' => $this->options['username'],
                            'apiKey'   => $this->options['apiKey']
                        )
                    )
                )
            )
        );

        try
        {
            /**
             * @var Response $response
             */
            $response = $this->client->send($request);
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 401)
            {
                throw new StorageException(
                    "Unable to perform Rackspace authorization using given credentials."
                );
            }

            throw $exception;
        }

        $response = json_decode((string)$response->getBody(), 1);

        foreach ($response['access']['serviceCatalog'] as $location)
        {
            if ($location['name'] == 'cloudFiles')
            {
                foreach ($location['endpoints'] as $server)
                {
                    $this->regions[$server['region']] = $server['publicURL'];
                }
            }
        }

        if (!isset($response['access']['token']['id']))
        {
            throw new StorageException("Unable to fetch rackspace auth token.");
        }

        $this->authToken = $response['access']['token']['id'];

        if ($this->options['cache'])
        {
            $this->cache->set(
                $this->options['username'] . '@rackspace-token',
                $this->authToken,
                self::CACHE_LIFETIME
            );

            $this->cache->set(
                $this->options['username'] . '@rackspace-regions',
                $this->regions,
                self::CACHE_LIFETIME
            );
        }

        return true;
    }

    /**
     * Flush existed token and reconnect, can be required if token has expired.
     */
    protected function reconnect()
    {
        $this->authToken = null;
        $this->connect();
    }

    /**
     * @param                  $method
     * @param StorageContainer $container
     * @param                  $name
     * @param array            $headers
     * @return RequestInterface
     */
    protected function buildRequest($method, StorageContainer $container, $name, array $headers = array())
    {
        //Adding auth headers
        $headers += array(
            'X-Auth-Token' => $this->authToken,
            'Date'         => gmdate('D, d M Y H:i:s T')
        );

        return new Request($method, $this->buildUri($container, $name), $headers);
    }

    protected function buildUri(StorageContainer $container, $name)
    {
        if (empty($container->options['region']))
        {
            throw new StorageException("Every rackspace container should have specified region.");
        }

        $region = $container->options['region'];
        if (!isset($this->regions[$region]))
        {
            throw new StorageException("'{$region}' region is not supported by Rackspace.");
        }

        return new Uri(
            $this->regions[$region] . '/' . $container->options['container'] . '/' . rawurlencode($name)
        );
    }
}