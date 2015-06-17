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
use GuzzleHttp\Psr7\Uri;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Components\Storage\StorageManager;
use Spiral\Components\Storage\StorageServer;

abstract class AmazonServer extends StorageServer
{
    /**
     * Storage component.
     *
     * @invisible
     * @var StorageManager
     */
    protected $storage = null;

    /**
     * File component.
     *
     * @invisible
     * @var FileManager
     */
    protected $file = null;

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
     * @param array          $options Storage connection options.
     * @param StorageManager $storage StorageManager component.
     * @param FileManager    $file    FileManager component.
     */
    public function __construct(array $options, StorageManager $storage, FileManager $file)
    {
        $this->options = $options + $this->options;
        $this->storage = $storage;
        $this->file = $file;

        //Some options can be passed directly for guzzle client
        $this->client = $client = new Client($this->options);
    }

    protected function createRequest(StorageContainer $container, $name)
    {
        $uri = new Uri($this->options['server'] . '/' . $container->options['bucket'] . '/' . rawurlencode($name));

    }
} 