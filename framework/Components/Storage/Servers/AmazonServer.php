<?php
/**
 * Spiral Framework, SpiralScout LLC.
 *
 * @package   spiralFramework
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2011
 */
namespace Spiral\Components\Storage\Servers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageServer;

class AmazonServer extends StorageServer
{
    /**
     * Default mimetype to be used when nothing else can be applied.
     */
    const DEFAULT_MIMETYPE = 'application/octet-stream';

    /**
     * Guzzle client.
     *
     * @var Client
     */
    protected $client = null;

    /**
     * Server configuration, connection options, auth keys and certificates.
     *
     * @var array
     */
    protected $options = array(
        'server'    => 'https://s3.amazonaws.com',
        'timeout'   => 0,
        'accessKey' => '',
        'secretKey' => ''
    );

    /**
     * Every server represent one virtual storage which can be either local, remove or cloud based.
     * Every adapter should support basic set of low-level operations (create, move, copy and etc).
     *
     * @param FileManager $file    FileManager component.
     * @param array       $options Storage connection options.
     */
    public function __construct(FileManager $file, array $options)
    {
        $this->options = $options + $this->options;
        $this->file = $file;

        //Some options can be passed directly for guzzle client
        $this->client = new Client($this->options);
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
            $response = $this->client->send($this->createRequest('HEAD', $container, $name));
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() == 404)
            {
                return false;
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

        $request = $this->createRequest(
            'PUT',
            $container,
            $name,
            array(
                'Content-MD5'  => base64_encode(md5_file($this->resolveFilename($origin), true)),
                'Content-Type' => $mimetype
            ),
            array(
                'Acl'          => $container->options['public'] ? 'public-read' : 'private',
                'Content-Type' => $mimetype
            )
        );

        $response = $this->client->send($request->withBody($this->resolveStream($origin)));

        return $response->getStatusCode() == 200;
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
        try
        {
            $response = $this->client->send(
                $this->createRequest('GET', $container, $name)
            );
        }
        catch (ClientException $exception)
        {
            //Reasonable?
            if ($exception->getCode() != 404)
            {
                throw $exception;
            }

            return false;
        }

        return $response->getBody();
    }

    /**
     * Remove storage object without changing it's own container. This operation does not require
     * object recreation or download and can be performed on remote server.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $oldname      Relative object name.
     * @param string           $newname   New object name.
     * @return bool
     */
    public function rename(StorageContainer $container, $oldname, $newname)
    {
        try
        {
            $this->client->send(
                $this->createRequest('PUT', $container, $newname, array(), array(
                    'Acl'         => $container->options['public'] ? 'public-read' : 'private',
                    'Copy-Source' => $this->buildUri($container, $oldname)->getPath()
                ))
            );
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() != 404)
            {
                throw $exception;
            }

            return false;
        }

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
        $this->client->send($this->createRequest('DELETE', $container, $name));
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
        if ($container->options['bucket'] == $destination->options['bucket'])
        {
            return true;
        }

        try
        {
            $this->client->send(
                $this->createRequest('PUT', $destination, $name, array(), array(
                    'Acl'         => $destination->options['public'] ? 'public-read' : 'private',
                    'Copy-Source' => $this->buildUri($container, $name)->getPath()
                ))
            );
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() != 404)
            {
                throw $exception;
            }

            return false;
        }

        return true;
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

        return false;
    }

    /**
     * @param                  $method
     * @param StorageContainer $container
     * @param                  $name
     * @param array            $headers
     * @param array            $commands
     * @return RequestInterface
     */
    protected function createRequest(
        $method,
        StorageContainer $container,
        $name,
        array $headers = array(),
        array $commands = array()
    )
    {
        $headers += array(
            'Date'         => gmdate('D, d M Y H:i:s T'),
            'Content-MD5'  => '',
            'Content-Type' => ''
        );

        $packedCommands = $this->packCommands($commands);

        return $this->signRequest(
            new Request($method, $this->buildUri($container, $name), $headers + $packedCommands),
            $packedCommands
        );
    }

    protected function buildUri(StorageContainer $container, $name)
    {
        return new Uri(
            $this->options['server'] . '/' . $container->options['bucket'] . '/' . rawurlencode($name)
        );
    }

    protected function packCommands(array $commands)
    {
        $headers = array();
        foreach ($commands as $command => $value)
        {
            $headers['X-Amz-' . $command] = $value;
        }

        return $headers;
    }

    protected function signRequest(RequestInterface $request, array $packedCommands = array())
    {
        $signature = array(
            $request->getMethod(),
            $request->getHeaderLine('Content-MD5'),
            $request->getHeaderLine('Content-Type'),
            $request->getHeaderLine('Date')
        );

        //todo: improve it
        $normalizedCommands = array();
        foreach ($packedCommands as $command => $value)
        {
            if (!empty($value))
            {
                $normalizedCommands[] = strtolower($command) . ':' . $value;
            }
        }

        if ($normalizedCommands)
        {
            sort($normalizedCommands);
            $signature[] = join("\n", $normalizedCommands);
        }

        $signature[] = $request->getUri()->getPath();

        return $request->withAddedHeader(
            'Authorization',
            'AWS ' . $this->options['accessKey'] . ':' . base64_encode(
                hash_hmac('sha1', join("\n", $signature), $this->options['secretKey'], true)
            )
        );
    }
} 