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
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageServer;

class AmazonServer extends StorageServer
{
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
     * Guzzle client.
     *
     * @var Client
     */
    protected $client = null;

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

        //Some options can be passed directly for guzzle client
        $this->client = new Client($this->options);
    }

    /**
     * Check if given object (name) exists in specified container. Method should never fail if file
     * not exists and will return bool in any condition.
     *
     * @param StorageContainer $container Container instance associated with specific server.
     * @param string           $name      Storage object name.
     * @return bool|ResponseInterface
     * @throws ClientException
     */
    public function exists(StorageContainer $container, $name)
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

            //Something wrong with connection
            throw $exception;
        }

        if ($response->getStatusCode() !== 200)
        {
            return false;
        }

        return $response;
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
        if (empty($response = $this->exists($container, $name)))
        {
            return false;
        }

        return (int)$response->getHeaderLine('Content-Length');
    }

    /**
     * Upload storage object using given filename or stream. Method can return false in case of failed
     * upload or thrown custom exception if needed.
     *
     * @param StorageContainer       $container Container instance.
     * @param string                 $name      Given storage object name.
     * @param string|StreamInterface $origin    Local filename or stream to use for creation.
     * @return bool
     * @throws ClientException
     * @throws ServerException
     */
    public function put(StorageContainer $container, $name, $origin)
    {
        if (empty($mimetype = \GuzzleHttp\Psr7\mimetype_from_filename($name)))
        {
            $mimetype = self::DEFAULT_MIMETYPE;
        }

        $request = $this->buildRequest(
            'PUT',
            $container,
            $name,
            array(
                'Content-MD5'  => base64_encode(md5_file($this->castFilename($origin), true)),
                'Content-Type' => $mimetype
            ),
            array(
                'Acl'          => $container->options['public'] ? 'public-read' : 'private',
                'Content-Type' => $mimetype
            )
        );

        return $this->client->send(
            $request->withBody($this->castStream($origin))
        )->getStatusCode() == 200;
    }

    /**
     * Get temporary read-only stream used to represent remote content. This method is very similar
     * to localFilename, however in some cases it may store data content in memory.
     *
     * Method should return false or thrown an exception if stream can not be allocated.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return StreamInterface|false
     * @throws ClientException
     * @throws ServerException
     */
    public function getStream(StorageContainer $container, $name)
    {
        try
        {
            $response = $this->client->send($this->buildRequest('GET', $container, $name));
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() != 404)
            {
                //Some authorization or other error
                throw $exception;
            }

            return false;
        }

        return $response->getBody();
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
     * @throws ClientException
     * @throws ServerException
     */
    public function rename(StorageContainer $container, $oldname, $newname)
    {
        try
        {
            $this->client->send($this->buildRequest(
                'PUT',
                $container,
                $newname,
                array(),
                array(
                    'Acl'         => $container->options['public'] ? 'public-read' : 'private',
                    'Copy-Source' => $this->buildUri($container, $oldname)->getPath()
                )
            ));
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() != 404)
            {
                //Some authorization or other error
                throw $exception;
            }

            return false;
        }

        $this->delete($container, $oldname);

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
        $this->client->send($this->buildRequest('DELETE', $container, $name));
    }

    /**
     * Copy object to another internal (under same server) container, this operation may not
     * require file download and can be performed remotely.
     *
     * Method should return false or thrown an exception if object can not be copied.
     *
     * @param StorageContainer $container   Container instance.
     * @param StorageContainer $destination Destination container (under same server).
     * @param string           $name        Storage object name.
     * @return bool
     */
    public function copy(StorageContainer $container, StorageContainer $destination, $name)
    {
        try
        {
            $this->client->send($this->buildRequest(
                'PUT',
                $destination,
                $name,
                array(),
                array(
                    'Acl'         => $destination->options['public'] ? 'public-read' : 'private',
                    'Copy-Source' => $this->buildUri($container, $name)->getPath()
                )
            ));
        }
        catch (ClientException $exception)
        {
            if ($exception->getCode() != 404)
            {
                //Some authorization or other error
                throw $exception;
            }

            return false;
        }

        return true;
    }

    /**
     * Create instance of UriInterface based on provided container instance and storage object name.
     *
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @return Uri
     */
    protected function buildUri(StorageContainer $container, $name)
    {
        return new Uri(
            $this->options['server'] . '/' . $container->options['bucket'] . '/' . rawurlencode($name)
        );
    }

    /**
     * Helper method used to create signed amazon request with correct set of headers.
     *
     * @param string           $method    Http method.
     * @param StorageContainer $container Container instance.
     * @param string           $name      Storage object name.
     * @param array            $headers   Request headers.
     * @param array            $commands  Amazon commands associated with values.
     * @return RequestInterface
     */
    protected function buildRequest(
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

    /**
     * Generate request headers based on provided set of amazon commands.
     *
     * @param array $commands
     * @return array
     */
    protected function packCommands(array $commands)
    {
        $headers = array();
        foreach ($commands as $command => $value)
        {
            $headers['X-Amz-' . $command] = $value;
        }

        return $headers;
    }

    /**
     * Sign amazon request.
     *
     * @param RequestInterface $request
     * @param array            $packedCommands Headers generated based on request commands, see
     *                                         packCommands() method for more information.
     * @return RequestInterface
     */
    protected function signRequest(RequestInterface $request, array $packedCommands = array())
    {
        $signature = array(
            $request->getMethod(),
            $request->getHeaderLine('Content-MD5'),
            $request->getHeaderLine('Content-Type'),
            $request->getHeaderLine('Date')
        );

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