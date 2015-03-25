<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers\Amazon;

use Spiral\Components\Storage\Servers\Traits\StorageQueryTrait;
use Spiral\Components\Storage\StorageContainer;
use Spiral\Support\Curl\CurlException;
use Spiral\Support\Curl\CurlQuery;

class S3Query extends CurlQuery
{
    /**
     * Common storage query abilities.
     */
    use StorageQueryTrait;

    /**
     * Server configuration, connection options, auth keys and certificates.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Target object (bucket + object name)
     *
     * @var string
     */
    protected $objectURL = '';

    /**
     * Amazon commands.
     *
     * @var array
     */
    protected $commands = array();

    /**
     * New rest\Query class, can be used to perform various requests to external api and websites, Query class can be
     * extended to support additional syntax, response types or define custom behaviour. Https requests can only be made if
     * they are supported by server environment.
     *
     * All requests will be made using CURL extension.
     *
     * @param array            $options   Amazon server options.
     * @param StorageContainer $container Container instance.
     * @param string           $name      Relative object name.
     * @param string           $method    HTTP method to be used.
     * @throws CurlException
     */
    public function __construct($options, StorageContainer $container, $name, $method)
    {
        $this->objectURL = '/' . $container->options['bucket'] . '/' . rawurldecode($name);
        $this->options = $options;

        $this->headers = array(
            'Host'         => $options['server'],
            'Date'         => gmdate('D, d M Y H:i:s T'),
            'Content-MD5'  => '',
            'Content-Type' => ''
        );

        //Full Query URL
        parent::__construct(($options['secured'] ? 'https://' : 'http://') . $options['server'] . $this->objectURL, $method);
    }

    /**
     * Create new amazon command header.
     *
     * @param string $command Amazon command.
     * @param string $value   Command value.
     * @return static
     */
    public function command($command, $value)
    {
        $this->commands['x-amz-' . $command] = $value;

        return $this->setHeader('x-amz-' . $command, $value);
    }

    /**
     * Generate signature for signed request.
     *
     * @return string
     */
    protected function buildSignature()
    {
        $signature = array(
            $this->method,
            $this->headers['Content-MD5'],
            $this->headers['Content-Type'],
            $this->headers['Date']
        );

        $commands = array();
        foreach ($this->commands as $command => $value)
        {
            if ($value !== '')
            {
                $commands[] = strtolower($command) . ':' . $value;
            }
        }

        if ($commands)
        {
            sort($commands);
            $signature[] = join("\n", $commands);
        }

        $signature[] = $this->objectURL;

        return base64_encode(hash_hmac('sha1', join("\n", $signature), $this->options['secretKey'], true));
    }

    /**
     * Generate list of headers to send to CURL request, can be extended to perform additional headers logic, for example
     * signatures or dynamic timestamps.
     *
     * @return array
     */
    protected function buildHeaders()
    {
        $result = array('Authorization: AWS ' . $this->options['accessKey'] . ':' . $this->buildSignature());
        $result += parent::buildHeaders();

        return $result;
    }

    /**
     * Custom method which can be extended by Query children to implement custom CURL setup logic.
     *
     * This method will be called before CURL request made, but after POST and GET data
     * were set. Headers will be set after this method.
     *
     * @param mixed $curl
     */
    protected function prepareCURL($curl)
    {
        if ($this->options['secured'] && $this->options['certificate'])
        {
            curl_setopt($curl, CURLOPT_CAINFO, $this->options['certificate']);
        }
        else
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }

        switch ($this->method)
        {
            case 'PUT':
                if ($this->localFilename)
                {
                    $this->fileResource = fopen($this->localFilename, 'rb');
                    curl_setopt($curl, CURLOPT_PUT, true);
                    curl_setopt($curl, CURLOPT_INFILESIZE, filesize($this->localFilename));
                    curl_setopt($curl, CURLOPT_INFILE, $this->fileResource);
                }
                else
                {
                    curl_setopt($curl, CURLOPT_NOBODY, true);
                }
                break;
            case 'GET':
                if ($this->localFilename)
                {
                    $this->fileResource = fopen($this->localFilename, 'wb');
                    curl_setopt($curl, CURLOPT_FILE, $this->fileResource);
                }
                break;
            case 'HEAD':
            case 'DELETE':
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
        }
    }
}