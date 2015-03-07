<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Storage\Servers\Traits;

trait StorageQueryTrait
{
    /**
     * File resource handlers to write or read from local filesystem.
     *
     * @var resource
     */
    protected $fileResource = '';

    /**
     * Linked local filename for downloading/uploading.
     *
     * @var string
     */
    protected $localFilename = '';

    /**
     * Add local filename for downloading or uploading.
     *
     * @param string $filename
     * @return static
     */
    public function filename($filename)
    {
        $this->localFilename = $filename;

        return $this;
    }

    /**
     * Response status code.
     *
     * @return mixed|null
     */
    abstract public function getHttpStatus();

    /**
     * List of all response headers, every header name will be lowercased.
     *
     * @return array
     */
    abstract public function getResponseHeaders();

    /**
     * Prepare result for output, output of cloud operation is list of array headers and status.
     *
     * @param mixed $result
     * @return mixed
     */
    protected function processResult($result)
    {
        if ($this->fileResource)
        {
            fclose($this->fileResource);
            $this->fileResource = null;
        }

        return $this->getResponseHeaders() + array('status' => $this->getHTTPStatus(), 'content' => $result);
    }
}