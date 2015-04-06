<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers\Rackspace;

use Spiral\Components\Storage\Servers\Traits\StorageQueryTrait;
use Spiral\Support\Curl\CurlException;
use Spiral\Support\Curl\CurlQuery;

class RackspaceQuery extends CurlQuery
{
    /**
     * Common storage query abilities.
     */
    use StorageQueryTrait;

    /**
     * New rest\Query class, can be used to perform various requests to external api and websites,
     * Query class can be extended to support additional syntax, response types or define custom
     * behaviour. Https requests can only be made if they are supported by server environment.
     *
     * All requests will be made using CURL extension.
     *
     * @param array  $options Rackspace server options.
     * @param string $URL     URL has to be requested.
     * @param string $method  HTTP method to be used.
     * @throws CurlException
     */
    public function __construct($options, $URL, $method)
    {
        $this->options = $options;
        parent::__construct($URL, $method);
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
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
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
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
            case 'AUTHORIZE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
            case 'COPY':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'COPY');
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
        }
    }
}