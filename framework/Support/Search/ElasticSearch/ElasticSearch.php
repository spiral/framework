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

class ElasticSearch extends Component
{
    /**
     * Elastic search connection options.
     *
     * @var array
     */
    protected $options = array(
        'server' => 'http://localhost:9200'
    );

    /**
     * Default index options to be joined with custom options set.
     *
     * @var array
     */
    protected $defaultOptions = array(
        'settings' => array(
            'filter'   => array(
                'snowball' => array(
                    'type'     => 'snowball',
                    'language' => 'English'
                )
            ),
            'analyzer' => array(
                'index_analyzer'  => array(
                    'tokenizer' => 'nGram',
                    'filter'    => array('lowercase', 'snowball')
                ),
                'search_analyzer' => array(
                    'tokenizer' => 'nGram',
                    'filter'    => array('lowercase', 'snowball')
                )
            )
        ),
        'types'    => array()
    );

    /**
     * ElasticSearch class represent simple wrapper to send requests to specified elasticSearch
     * backend. This class support results and requests packing, custom command performing and index
     * creation.
     *
     * @param string $server
     */
    public function __construct($server = 'http://localhost:9200')
    {
        $this->options['server'] = $server;
    }

    /**
     * Flush and recreate index with specified options and associate mapping after. Options array
     * should include two sections, "settings" and "types", where types is default elastic search
     * type mapping and index specify base options, such as analyzers, filters and etc. Default index
     * options includes index and search tokenizers to correctly process requests and items in english
     * language.
     *
     * Existed index and all data will be erased.
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/_creating_an_index.html#_creating_an_index
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/custom-analyzers.html
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-types.html
     * @param string $index
     * @param array  $options
     * @throws ElasticException
     */
    public function initiateIndex($index, array $options)
    {
        $this->query('delete', $index);

        $options = $options + $this->defaultOptions;
        $result = $this->query('put', $index, array(
            'index' => $options['settings']
        ));

        if (isset($result['error']))
        {
            throw new ElasticException($result['error']);
        }

        if (!empty($options['types']))
        {
            foreach ($options['types'] as $type => $definition)
            {
                $result = $this->query('put', $index . '/' . $type . '/_mapping', array(
                    $type => $definition
                ));

                if (isset($result['error']))
                {
                    throw new ElasticException($result['error']);
                }
            }
        }
    }

    /**
     * Run custom query to elasticSearch search.
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/running-elasticsearch.html#running-elasticsearch
     * @param string $method    HTTP method.
     * @param string $url       URL string.
     * @param array  $data      JSON to be sent.
     * @param array  $queryData GET data to be sent.
     * @return array
     */
    public function query($method, $url, array $data = array(), array $queryData = array())
    {
        $query = JsonQuery::make(array(
            'url'    => $this->options['server'] . '/' . $url,
            'method' => strtoupper($method)
        ));

        $data && $query->setQuery($data);

        foreach ($queryData as $name => $value)
        {
            $query->setQuery($name, $value);
        }

        return $query->run();
    }

    /**
     * Indexing document with specified id, if id already taken new revision will be created and
     * previous document data will be overwritten.
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/index-doc.html
     * @param string $index    Index name.
     * @param string $type     Registered document type.
     * @param mixed  $id       Unique document id.
     * @param array  $document Document data.
     * @return array
     */
    public function indexDocument($index, $type, $id, array $document)
    {
        return $this->query('PUT', $index . '/' . $type . '/' . $id, $document);
    }

    /**
     * Remove document and it's all revisions from specified index by id.
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/delete-doc.html
     * @param string $index Index name.
     * @param string $type
     * @param string $id    Unique document id.
     * @return mixed
     */
    public function removeDocument($index, $type, $id)
    {
        return $this->query('DELETE', $index . '/' . $type . '/' . $id);
    }

    /**
     * Run ElasticSearch query. Query will be compiled using query, filters and sortings array.
     *
     * Examples:
     *
     * $query['bool'] = array(
     *      array(
     *          'simple_query_string' => array(
     *              'query' => $query
     *      )
     * );
     *
     * $query['bool'] = array(
     *      'must' => array(
     *          array(
     *              'simple_query_string' => array(
     *                  'query' => $query
     *          ),
     *          array(
     *              'term' => array(
     *                  'field' => 'value'
     *              )
     *          )
     *      )
     * );
     *
     * $filters['bool']['must'][] = array(
     *      'term' => array(
     *          'onSale' => true
     *      )
     * );
     *
     * $sorting = array(
     *      'fieldA' => array('order' => 'asc'),
     *      'fieldB' => array('order' => 'desc')
     * );
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/guide/current/bool-query.html
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-filters.html
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-term-filter.html#query-dsl-term-filter
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-sort.html#_sort_values
     * @param array  $query   Query array request.
     * @param array  $filters Filters to apply.
     * @param array  $sorting Sorts to apply.
     * @param string $index   Index to search (all by default).
     * @param string $type    Types to search (all by default).
     * @param int    $offset  Records to skip.
     * @param int    $limit   Records per request (page).
     * @return SearchResult
     */
    public function runSearch(
        array $query = array(),
        array $filters = array(),
        array $sorting = array(),
        $index = null,
        $type = null,
        $offset = 0,
        $limit = 25
    )
    {
        $query = $this->buildQuery($query, $filters, $sorting, $offset, $limit);

        return SearchResult::make(array(
            'result' => $this->query(
                'get',
                trim($index . '/' . $type, '/') . '/_search',
                $query
            )
        ));
    }

    /**
     * Helper function to compile search query.
     *
     * @param array $query   Query array request.
     * @param array $filters Filters to apply.
     * @param array $sorting Sorts to apply.
     * @param int   $offset  Records to skip.
     * @param int   $limit   Records per request (page).
     * @return array
     */
    protected function buildQuery(
        array $query,
        array $filters = array(),
        array $sorting = array(),
        $offset = 0,
        $limit = 25
    )
    {
        $request = array('from' => $offset, 'size' => $limit);

        //Building request
        if (!empty($query) || !empty($filters) || !empty($sorting))
        {
            $request['query'] = array();
            if (!empty($filters))
            {
                //Filtered query
                $request['query']['filtered'] = array('filter' => $filters);

                $query && $request['query']['filtered']['query'] = $query;
            }
            else
            {
                $request['query'] = $query;
            }
        }

        if (!empty($sorting))
        {
            $request['sort'] = $sorting;
        }

        return $request;
    }
}