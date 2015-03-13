<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Search\ElasticSearch;

use Spiral\Support\Curl\CurlQuery;

class JsonQuery extends CurlQuery
{
    /**
     * Serializing POST data using JSON to send to elastic search server.
     *
     * @param array $query
     * @return static
     */
    public function setQuery(array $query)
    {
        $this->setRawPOST(json_encode($query));

        return $this;
    }

    /**
     * Custom method which can be extended by Query children to implement custom CURL response parsing
     * logic. Will be called after CURL request made, but only if request succeeded.
     *
     * @param mixed $result
     * @return mixed
     */
    protected function processResult($result)
    {
        return json_decode($result, true);
    }
}