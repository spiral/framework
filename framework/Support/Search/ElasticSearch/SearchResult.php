<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Search\ElasticSearch;

use Spiral\Core\Component;

class SearchResult extends Component implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Elastic search result body.
     *
     * @var array
     */
    public $result = array();

    /**
     * Simple class to represent data collect from elasticSearch search result response.
     *
     * @param array $result
     */
    public function __construct(array $result)
    {
        $this->result = $result;
    }

    /**
     * Array of found documents (hits). Use mapData argument to format document source to more convenient format.
     *
     * @param bool $mapData Include document id, index and type to document source.
     * @return array
     */
    public function getHits($mapData = false)
    {
        if (!isset($this->result['hits']['hits']))
        {
            return array();
        }

        if (!$mapData)
        {
            return $this->result['hits']['hits'];
        }

        $result = array();
        foreach ($this->result['hits']['hits'] as $hit)
        {
            $result[] = array(
                    '@id'    => $hit['_id'],
                    '@index' => $hit['_index'],
                    '@type'  => $hit['_type'],
                ) + $hit['_source'];
        }

        return $result;
    }

    /**
     * List of document ids retrieved via search query.
     *
     * @return array
     */
    public function getIDs()
    {
        if (!isset($this->result['hits']['hits']))
        {
            return array();
        }

        $result = array();
        foreach ($this->result['hits']['hits'] as $hit)
        {
            $result[] = $hit['_id'];
        }

        return $result;
    }

    /**
     * Total records found.
     *
     * @return string
     */
    public function getTotal()
    {
        if (isset($this->result['hits']['total']))
        {
            return $this->result['hits']['total'];
        }

        return 0;
    }

    /**
     * Count elements of an object.
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int
     */
    public function count()
    {
        return $this->getTotal();
    }

    /**
     * Retrieve an external iterator.
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getHits());
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    function jsonSerialize()
    {
        return $this->getHits();
    }
}