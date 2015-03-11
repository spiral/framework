<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Search\SphinxSearch;

use Spiral\Core\Component;

class SphinxSearch extends Component
{
    /**
     * Helper constants - commands.
     */
    const COMMAND_SEARCH   = 0;
    const COMMAND_EXCERPT  = 1;
    const COMMAND_UPDATE   = 2;
    const COMMAND_KEYWORDS = 3;
    const COMMAND_PERSIST  = 4;
    const COMMAND_STATUS   = 5;
    const COMMAND_QUERY    = 6;

    /**
     * Helper constants - client commands.
     */
    const CLIENT_COMMAND_SEARCH   = 0x116;
    const CLIENT_COMMAND_EXCERPT  = 0x100;
    const CLIENT_COMMAND_UPDATE   = 0x102;
    const CLIENT_COMMAND_KEYWORDS = 0x100;
    const CLIENT_COMMAND_STATUS   = 0x100;
    const CLIENT_COMMAND_QUERY    = 0x100;

    /**
     * Helper constants - statuses.
     */
    const STATUS_OK      = 0;
    const STATUS_ERROR   = 1;
    const STATUS_RETRY   = 2;
    const STATUS_WARNING = 3;

    /**
     * Available sphinx matching modes.
     *
     * @link http://sphinxsearch.com/docs/current.html#matching-modes
     */
    const MATCH_ALL       = 0;
    const MATCH_ANY       = 1;
    const MATCH_PHRASE    = 2;
    const MATCH_BOOLEAN   = 3;
    const MATCH_EXTENDED  = 4;
    const MATCH_FULL_SCAN = 5;
    const MATCH_EXTENDED2 = 6;

    /**
     * Ranking modes.
     *
     * proximityBM25 - default mode, phrase proximity major factor and BM25 minor one.
     * BM25          - statistical mode, BM25 ranking only (faster but worse quality).
     * None          - no ranking, all matches get a weight of 1 (Use it you want simply fetch results).
     * WordCount     - simple word-count weighting, rank is a weighted sum of per-field keyword occurrence counts.
     *
     * @link http://sphinxsearch.com/docs/current.html#weighting
     */
    const RANK_PROXIMITY_BM25 = 0;
    const RANK_BM25           = 1;
    const RANK_NONE           = 2;
    const RANK_WORD_COUNT     = 3;
    const RANK_PROXIMITY      = 4;
    const RANK_MATCH_ANY      = 5;
    const RANK_FIELD_MASK     = 6;

    /**
     * Sorting modes.
     *
     * @link http://sphinxsearch.com/docs/current.html#sorting-modes
     */
    const SORT_RELEVANCE      = 0;
    const SORT_ATTRIBUTE_DESC = 1;
    const SORT_ATTRIBUTE_ASC  = 2;
    const SORT_TIME           = 3;
    const SORT_EXTENDED       = 4;
    const SORT_EXPRESSION     = 5;

    /**
     * Attribute types.
     */
    const TYPE_INTEGER   = 1;
    const TIME_TIMESTAMP = 2;
    const TYPE_ORDINAL   = 3;
    const TYPE_BOOLEAN   = 4;
    const TYPE_FLOAT     = 5;
    const TYPE_LONG      = 6;
    const TYPE_MULTI     = 0x40000000;

    /**
     * Grouping functions.
     */
    const GROUP_BY_DAY            = 0;
    const GROUP_BY_WEEK           = 1;
    const GROUP_BY_MONTH          = 2;
    const GROUP_BY_YEAR           = 3;
    const GROUP_BY_ATTRIBUTE      = 4;
    const GROUP_BY_ATTRIBUTE_PAIR = 5;

    /**
     * Filtering types.
     *
     * @link http://sphinxsearch.com/docs/current.html#api-funcgroup-filtering
     */
    const FILTER_VALUES      = 0;
    const FILTER_RANGE       = 1;
    const FILTER_FLOAT_RANGE = 2;

    /**
     * Error message raised during last request.
     *
     * @var string
     */
    public $errorMessage = '';

    /**
     * Warning message raised during last requist.
     *
     * @var string
     */
    public $warningMessage = '';

    /**
     * Sphinx daemon connection.
     *
     * @var resource
     */
    protected $connection = null;

    /**
     * Sphinx host.
     *
     * @var string
     */
    protected $serverHost = '';

    /**
     * Sphinx port.
     *
     * @var bool|int
     */
    protected $serverPort = false;

    /**
     * Sphinx connection timeout.
     *
     * @var bool|int
     */
    protected $serverTimeout = false;

    /**
     * Max results returned via sphinx. Defined by option in sphinx.conf.
     *
     * @link http://sphinxsearch.com/docs/current.html
     * @var int
     */
    public $maxMatches = 2500;

    /**
     * Single query timeout.
     *
     * @var int
     */
    public $maxQueryTime = 0;

    /**
     * Next query matching mode.
     *
     * @link http://sphinxsearch.com/docs/current.html#matching-modes
     * @var int
     */
    protected $matchingMode = self::MATCH_EXTENDED2;

    /**
     * Next query ranking (weight) mode.
     *
     * @link http://sphinxsearch.com/docs/current.html#weighting
     * @var int
     */
    protected $rankingMode = self::RANK_PROXIMITY_BM25;

    /**
     * Next query sorting mode.
     *
     * @link http://sphinxsearch.com/docs/current.html#sorting-modes
     * @var int
     */
    protected $sortingMode = self::SORT_RELEVANCE;

    /**
     * Index weight used to sort results while searching for multiple indexes.
     *
     * @var array
     */
    protected $indexWeights = array();

    /**
     * Field weight while keyword or phrase found in multiple item locations.
     *
     * @var array
     */
    protected $fieldWeights = array();

    /**
     * Fields to retrieve from found objects, all by default.
     *
     * @var string
     */
    protected $fields = '*';

    /**
     * Records per load (page).
     *
     * @var int
     */
    protected $limit = 50;

    /**
     * Records to skip from beginning of results set, can not exceed maxMatches parameter.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Sorting field or expression.
     *
     * @var string
     */
    protected $selectSortBy = '';

    /**
     * Grouping mode (if enabled).
     *
     * @link http://sphinxsearch.com/docs/current.html#clustering
     * @var int
     */
    protected $groupFunction = self::GROUP_BY_DAY;

    /**
     * Current grouping expression.
     *
     * @link http://sphinxsearch.com/docs/current.html#clustering
     * @var string
     */
    protected $groupBy = "";

    /**
     * Group sorting.
     *
     * @var string
     */
    protected $groupSorting = "@group desc";

    /**
     * Group distinct field.
     *
     * @var string
     */
    protected $groupDistinct = "";

    /**
     * Fields should be applied to next search query.
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Amount of connection tries.
     *
     * @var int
     */
    public $retryCount = 0;

    /**
     * Delay between retry connections.
     *
     * @var int
     */
    public $retryDelay = 0;

    /**
     * Query requests.
     *
     * @var array
     */
    protected $requests = array();

    /**
     * Create sphinx client with specified server. Based on original client implementation from official SDK.
     *
     * @param string   $host    Sphinx host, localhost by default.
     * @param int      $port    Sphinx port, 3312 by default.
     * @param int|bool $timeout Sphinx connection timeout.
     */
    public function __construct($host = 'localhost', $port = 3312, $timeout = 10)
    {
        $this->serverHost = $host;
        $this->serverPort = (int)$port;
        $this->serverTimeout = $timeout;

        if ($host[0] == '/')
        {
            $this->serverHost = 'unix://' . $host;
            $this->serverPort = 0;

            return;
        }

        if (substr($host, 0, 7) == "unix://")
        {
            $this->serverHost = $host;
            $this->serverPort = 0;

            return;
        }
    }

    /**
     * Open socked connection to sphinx daemon.
     *
     * @return mixed
     */
    protected function getConnection()
    {
        if ($this->connection)
        {
            if (!@feof($this->connection))
            {
                return $this->connection;
            }

            $this->connection = null;
        }

        $this->errorMessage = '';
        $errorCode = false;
        if (!$this->serverTimeout)
        {
            $this->connection = fsockopen($this->serverHost, $this->serverPort, $errorCode, $this->errorMessage);
        }
        else
        {
            $this->connection = fsockopen($this->serverHost, $this->serverPort, $errorCode, $this->errorMessage, (int)$this->serverTimeout);
        }

        if (!$this->connection)
        {
            //Failed to connect
            $this->errorMessage = trim($this->errorMessage);
            $this->errorMessage = "Connection to $this->serverHost:$this->serverPort failed ($errorCode: $this->errorMessage).";

            return false;
        }

        /**
         * Original library:
         *
         * Send my version this is a subtle part. we must do it before (!) reading back from searchd. Because otherwise
         * under some conditions (reported on FreeBSD for instance)TCP stack could throttle write-write-read pattern because
         * of Nagle.
         */
        if (!$this->write(pack("N", 1), 4))
        {
            fclose($this->connection);
            $this->errorMessage = "Failed to send client protocol version.";

            return false;
        }

        //Checking version
        list(, $version) = $this->read('N*', 4);

        if ((int)$version < 1)
        {
            fclose($this->connection);
            $this->errorMessage = "Expected searchd protocol version 1+, got version '$version'.";

            return false;
        }

        return $this->connection;
    }

    /**
     * Write data to connection stream.
     *
     * @param string $data   Data to write.
     * @param int    $length Data length.
     * @return bool
     */
    protected function write($data, $length)
    {
        if (feof($this->connection) || fwrite($this->connection, $data, $length) !== $length)
        {
            $this->errorMessage = 'Connection unexpectedly closed (timed out?).';

            return false;
        }

        return true;
    }

    /**
     * Read data from stream and unpack it based on provided format.
     *
     * @param string $format Binary pack format.
     * @param int    $length Data length.
     * @return mixed
     */
    protected function read($format, $length)
    {
        if (feof($this->connection))
        {
            $this->errorMessage = 'Connection unexpectedly closed (timed out?).';

            return null;
        }

        return unpack($format, fread($this->connection, $length));
    }

    /**
     * Current selection limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Records to select per one request.
     *
     * @param int $limit Records per request.
     * @return static
     */
    public function setLimit($limit = 0)
    {
        $this->limit = (int)$limit;

        return $this;
    }

    /**
     * Current selection offset.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Records to skip from beginning of search selection.
     *
     * @param int $offset Records to skip.
     * @return static
     */
    public function setOffset($offset = 0)
    {
        $this->offset = (int)$offset;

        return $this;
    }

    /**
     * Fields to return, use comma to specify multiple fields. Can include @weight keywords and expressions.
     *
     * @param string $fields Fields to be selected.
     * @return static
     */
    public function selectFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set query matching mode.
     *
     * @link http://sphinxsearch.com/docs/current.html#matching-modes
     * @param int $mode
     * @return static
     */
    public function matchingMode($mode)
    {
        $this->matchingMode = $mode;

        return $this;
    }

    /**
     * Set query ranking mode.
     *
     * @link http://sphinxsearch.com/docs/current.html#sorting-modes
     * @param int $mode
     * @return static
     */
    public function rankingMode($mode)
    {
        $this->rankingMode = $mode;

        return $this;
    }

    /**
     * Set sorting mode and sorting fields.
     *
     * @link http://sphinxsearch.com/docs/current.html#sorting-modes
     * @param int    $mode
     * @param string $sortBy
     * @return static
     */
    public function sortingMode($mode, $sortBy = '')
    {
        if ($mode != self::SORT_RELEVANCE && !strlen($sortBy))
        {
            return $this;
        }

        $this->sortingMode = $mode;
        $this->selectSortBy = $sortBy;

        return $this;
    }

    /**
     * Fields weight for sorting.
     *
     * @param array $weights
     * @return static
     */
    public function fieldWeights(array $weights)
    {
        foreach ($weights as &$weight)
        {
            $weight = (int)$weight;
        }

        $this->fieldWeights = $weights;

        return $this;
    }

    /**
     * Index weight for sorting.
     *
     * @param array $weights
     * @return static
     */
    public function setIndexWeights($weights)
    {
        foreach ($weights as &$weight)
        {
            $weight = (int)$weight;
        }

        $this->indexWeights = $weights;

        return $this;
    }

    /**
     * Set grouping.
     *
     * @link http://sphinxsearch.com/docs/current.html#clustering
     * @param string $attribute    Grouping attribute.
     * @param int    $function     Group function.
     * @param string $groupSorting Group sorting.
     * @return static
     */
    public function groupBy($attribute, $function = self::GROUP_BY_ATTRIBUTE, $groupSorting = "@group desc")
    {
        $this->groupBy = $attribute;
        $this->groupFunction = (int)$function;
        $this->groupSorting = $groupSorting;

        return $this;
    }

    /**
     * Group distinct.
     *
     * @param string $attribute
     * @return static
     */
    public function groupDistinct($attribute)
    {
        $this->groupDistinct = (string)$attribute;

        return $this;
    }

    /**
     * Allow only attributes in values array (or outside array if exclude).
     *
     * @param string $attribute Field or expression.
     * @param array  $values    Values list.
     * @param bool   $exclude   All values except specified, false by default.
     * @return static
     */
    public function setFilter($attribute, array $values, $exclude = false)
    {
        foreach ($values as &$value)
        {
            $value = (int)$value;
        }

        $this->filters[] = array(
            'type'    => self::FILTER_VALUES,
            'name'    => (string)$attribute,
            'exclude' => (int)$exclude,
            'values'  => $values
        );

        return $this;
    }

    /**
     * Allow only attributes in numeric range (or outside range if exclude).
     *
     * @param string $attribute Field or expression.
     * @param int    $begin     Range beginning.
     * @param int    $end       Range end.
     * @param bool   $exclude   Exclude from range, false by default.
     * @return static
     */
    public function rangeFilter($attribute, $begin, $end, $exclude = false)
    {
        $this->filters[] = array(
            'type'    => self::FILTER_RANGE,
            'name'    => (string)$attribute,
            'exclude' => (int)$exclude,
            'begin'   => (int)$begin,
            'end'     => (int)$end
        );

        return $this;
    }

    /**
     * Allow only attributes in float range (or outside range if exclude).
     *
     * @param string $attribute Field or expression.
     * @param int    $begin     Range beginning.
     * @param int    $end       Range end.
     * @param bool   $exclude   Exclude from range, false by default.
     * @return static
     */
    public function floatRangeFilter($attribute, $begin, $end, $exclude = false)
    {
        $this->filters[] = array(
            'type'    => self::FILTER_FLOAT_RANGE,
            'name'    => (string)$attribute,
            'exclude' => (int)$exclude,
            'begin'   => (float)$begin,
            'end'     => (float)$end
        );

        return $this;
    }

    /**
     * Reset all specified filters.
     *
     * @return static
     */
    public function resetFilters()
    {
        $this->filters = array();

        return $this;
    }

    /**
     * Adding new query to queries stack, sphinx can perform multiple queries at same time.
     *
     * @param string $query Query expression.
     * @param string $index Target index (all indexes by default).
     * @return static
     */
    public function addQuery($query, $index = "*")
    {
        //Building request
        $request = pack('NNNNN',
            $this->offset,
            $this->limit,
            (int)$this->matchingMode,
            (int)$this->rankingMode,
            (int)$this->sortingMode
        );

        //Sort by
        $request .= $this->packString($this->selectSortBy);

        //Query
        $request .= $this->packString($query);

        //Weights
        $request .= pack('N', 0);

        //Target indexes
        $request .= $this->packString($index);

        //id64 range marker
        $request .= pack('N', 1);

        //Min ID, max ID (no limits, 64 bit)
        $request .= pack('NN', 0, 0) . pack('NN', 0, 0);

        //Filters
        $request .= pack('N', count($this->filters));
        foreach ($this->filters as $filter)
        {
            $request .= $this->packString($filter["name"]);
            $request .= pack("N", $filter["type"]);
            switch ($filter["type"])
            {
                case self::FILTER_VALUES:
                    $request .= pack("N", count($filter["values"]));
                    foreach ($filter["values"] as $value)
                    {
                        $request .= $this->pack64($value);
                    }
                    break;

                case self::FILTER_RANGE:
                    $request .= $this->pack64($filter["begin"]) . $this->pack64($filter["end"]);
                    break;

                case self::FILTER_FLOAT_RANGE:
                    $request .= $this->packFloat($filter["begin"]) . $this->packFloat($filter["end"]);
                    break;
            }
            $request .= pack("N", $filter["exclude"]);
        }

        //Group function
        $request .= pack('N', $this->groupFunction);

        //Group by
        $request .= $this->packString($this->groupBy);

        //Max matches
        $request .= pack("N", $this->maxMatches);

        //Group sorting
        $request .= $this->packString($this->groupSorting);

        //Cut off, retryCount, retryDelay
        $request .= pack("NNN", 0, (int)$this->retryCount, (int)$this->retryDelay);

        //Group distinct
        $request .= $this->packString($this->groupDistinct);

        //Anchor
        $request .= pack("N", 0);

        //Index weights
        $request .= pack("N", count($this->indexWeights));
        foreach ($this->indexWeights as $index => $weight)
        {
            $request .= $this->packString($index) . pack("N", $weight);
        }

        //Max query time
        $request .= pack("N", $this->maxQueryTime);

        //Field weights
        $request .= pack("N", count($this->fieldWeights));
        foreach ($this->fieldWeights as $attribute => $weight)
        {
            $request .= $this->packString($attribute) . pack("N", $weight);
        }

        //No comments
        $request .= pack("N", 0);

        //Attribute overrides
        $request .= pack("N", 0);

        //Select fields
        $request .= $this->packString($this->fields);

        $this->requests[] = $request;

        return $this;
    }

    /**
     * Single sphinx search query request. If include attributes=false, result will be backed as itemID=weight.
     *
     * @param string      $query             Query expression.
     * @param string      $index             Target index (all indexes by default).
     * @param string|bool $includeAttributes True to fetch and parse found item attributes.
     * @return array
     */
    public function query($query, $index = "*", $includeAttributes = true)
    {
        $this->addQuery($query, $index);
        $results = $this->runQueries($includeAttributes);

        if (!is_array($results))
        {
            return false;
        }

        if (!empty($results[0]))
        {
            $this->errorMessage = $results[0]["error"];
            $this->warningMessage = $results[0]["warning"];

            if ($results[0]["status"] == self::STATUS_ERROR)
            {
                return false;
            }
            else
            {
                return $results[0];
            }
        }

        return false;
    }

    /**
     * Run multiple defined queries. If include attributes=false, result will be backed as itemID=weight. Use addQuery()
     * to specify queries before using this method.
     *
     * @param string|bool $includeAttributes True to fetch and parse found item attributes.
     * @return array|bool
     */
    public function runQueries($includeAttributes = true)
    {
        if (empty($this->requests))
        {
            $this->errorMessage = "No queries found.";

            return false;
        }

        if (!$this->getConnection())
        {
            return false;
        }

        // send query, get response
        $count = count($this->requests);
        $request = join('', $this->requests);
        $length = 4 + strlen($request);

        //Header
        $request = pack("nnNN", self::COMMAND_SEARCH, self::CLIENT_COMMAND_SEARCH, $length, $count) . $request;

        if (!$this->write($request, $length + 8))
        {
            return false;
        }

        $response = $this->readResponse();

        //No request
        $this->requests = array();

        // parse and return response
        return $this->parseResponse((string)$response, $count, !$includeAttributes);
    }

    /**
     * Read sphinx response.
     *
     * @return bool|string
     */
    protected function readResponse()
    {
        list ($status, $version, $length) = array_values($this->read('n2a/Nb', 8));
        $response = '';
        $bytesLeft = $length;

        while ($bytesLeft > 0 && !feof($this->connection))
        {
            $chunk = fread($this->connection, $bytesLeft);
            if ($chunk)
            {
                $response .= $chunk;
                $bytesLeft -= strlen($chunk);
            }
        }

        fclose($this->connection);
        if (!$response || strlen($response) != $length)
        {
            $this->errorMessage = $length ? "Failed to read searchd response (code $status, version $version, readed " .
                strlen($response) . "/$length)." : "Received zero-sized searchd response.";

            return false;
        }

        if ($status == self::STATUS_WARNING)
        {
            list(, $warning) = unpack("N*", substr($response, 0, 4));
            $this->warningMessage = substr($response, 4, $warning);

            return substr($response, 4 + $warning);
        }

        if ($status == self::STATUS_ERROR)
        {
            $this->errorMessage = "Searchd error: " . substr($response, 4) . ".";

            return false;
        }

        if ($status == self::STATUS_RETRY)
        {
            $this->errorMessage = "Temporary searchd error: " . substr($response, 4) . ".";

            return false;
        }

        if ($status != self::STATUS_OK)
        {
            $this->errorMessage = "Unknown status code: '$status'.";

            return false;
        }

        return $response;
    }

    /**
     * Parse response to readable array.
     *
     * @param string $response
     * @param int    $count
     * @param bool   $noAttributes
     * @return array
     */
    protected function parseResponse($response, $count, $noAttributes)
    {
        $results = array();
        $i = 0;
        $length = strlen($response);

        for ($subQuery = 0; $subQuery < $count && $i < $length; $subQuery++)
        {
            $results[] = array();
            $result = &$results[$subQuery];

            $result['status'] = 0;
            $result['error'] = '';
            $result['warning'] = '';
            $result['fields'] = array();
            $result['attributes'] = array();
            $result['result'] = array();

            //Status
            list(, $status) = unpack("N*", substr($response, $i, 4));

            $i += 4;
            $result["status"] = $status;

            if ($status != self::STATUS_OK)
            {
                list(, $l) = unpack("N*", substr($response, $i, 4));
                $i += 4;
                $message = substr($response, $i, $l);
                $i += $l;

                if ($status == self::STATUS_WARNING)
                {
                    $result["warning"] = $message;
                }
                else
                {
                    $result["error"] = $message;
                    continue;
                }
            }

            $fields = array();
            $attributes = array();

            list(, $countFields) = unpack("N*", substr($response, $i, 4));
            $i += 4;
            while ($countFields-- > 0 && $i < $length)
            {
                list(, $l) = unpack("N*", substr($response, $i, 4));
                $i += 4;
                $fields[] = substr($response, $i, $l);
                $i += $l;
            }
            $result['fields'] = $fields;

            list(, $countAttributes) = unpack("N*", substr($response, $i, 4));
            $i += 4;
            while ($countAttributes-- > 0 && $i < $length)
            {
                list(, $l) = unpack("N*", substr($response, $i, 4));
                $i += 4;
                $name = substr($response, $i, $l);
                $i += $l;
                list(, $type) = unpack("N*", substr($response, $i, 4));
                $i += 4;
                $attributes[$name] = $type;
            }
            $result["attributes"] = $attributes;

            //Matches count
            list(, $itemsCount) = unpack("N*", substr($response, $i, 4));
            $i += 4;
            list(, $use64) = unpack("N*", substr($response, $i, 4));
            $i += 4;

            $result['count'] = $itemsCount;

            $item = -1;
            while ($itemsCount-- > 0 && $i < $length)
            {
                $item++;

                if ($use64)
                {
                    $document = $this->unpack64u(substr($response, $i, 8));
                    $i += 8;
                    list(, $weight) = unpack("N*", substr($response, $i, 4));
                    $i += 4;
                }
                else
                {
                    list ($document, $weight) = array_values(unpack("N*N*", substr($response, $i, 8)));
                    $document = $this->fix64u($document);
                    $i += 8;
                }

                $weight = sprintf("%u", $weight);

                if ($noAttributes)
                {
                    $result['result'][$document] = $weight;
                }
                else
                {
                    $result['result'][$document] = array();
                }

                $values = array(
                    'weight' => $weight
                );

                foreach ($attributes as $name => $type)
                {
                    if ($type == self::TYPE_LONG)
                    {
                        if ($noAttributes)
                        {
                            $i += 8;
                            continue;
                        }

                        $values[$name] = $this->unpack64(substr($response, $i, 8));
                        $i += 8;
                        continue;
                    }

                    if ($type == self::TYPE_FLOAT)
                    {
                        if ($noAttributes)
                        {
                            $i += 4;
                            continue;
                        }

                        list(, $value) = unpack("N*", substr($response, $i, 4));
                        $i += 4;
                        list(, $float) = unpack("f*", pack("L", $value));
                        $values[$name] = $float;
                        continue;
                    }

                    list(, $value) = unpack("N*", substr($response, $i, 4));
                    $i += 4;

                    if ($type & self::TYPE_MULTI)
                    {
                        $values[$name] = array();
                        $countValues = $value;
                        while ($countValues-- > 0 && $i < $length)
                        {
                            list(, $value) = unpack("N*", substr($response, $i, 4));
                            $i += 4;
                            $values[$name][] = $this->fix64u($value);
                        }
                    }
                    else
                    {
                        $values[$name] = $this->fix64u($value);
                    }
                }

                if (!$noAttributes)
                {
                    $result['result'][$document] = $values;
                }
            }

            list ($available, $found, $elapsed, $keywords) = array_values(unpack("N*N*N*N*", substr($response, $i, 16)));
            $result["available"] = sprintf("%u", $available);
            $result["found"] = sprintf("%u", $found);
            $result["elapsed"] = sprintf("%.3f", $elapsed / 1000);
            $i += 16;

            while ($keywords-- > 0 && $i < $length)
            {
                list(, $l) = unpack("N*", substr($response, $i, 4));
                $i += 4;
                $keyword = substr($response, $i, $l);
                $i += $l;
                list ($documents, $usages) = array_values(unpack("N*N*", substr($response, $i, 8)));
                $i += 8;
                $result["keywords"][$keyword] = array(
                    "documents" => sprintf("%u", $documents),
                    "usages"    => sprintf("%u", $usages)
                );
            }
        }

        return $results;
    }

    /**
     * String escaping for search.
     *
     * @param string $string
     * @return string
     */
    public static function escape($string)
    {
        return str_replace(
            array('\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/', '^', '$', '='),
            array('\\\\', '\(', '\)', '\|', '\-', '\!', '\@', '\~', '\"', '\&', '\/', '\^', '\$', '\='),
            $string
        );
    }

    /**
     * Pack string (length included).
     *
     * @param string $string
     * @return string
     */
    protected function packString($string)
    {
        return pack('N', strlen((string)$string)) . (string)$string;
    }

    /**
     * Pack float value.
     *
     * @param float $float
     * @return string
     */
    protected function packFloat($float)
    {
        $float = pack("f", $float);
        list(, $float) = unpack("L*", $float);

        return pack("N", $float);
    }

    /**
     * Pack 64 integer (signed).
     *
     * @param int $int
     * @return string
     * @throws \ErrorException
     */
    protected function pack64($int)
    {
        if (PHP_INT_SIZE >= 8)
        {
            $int = (int)$int;

            return pack("NN", $int >> 32, $int & 0xFFFFFFFF);
        }

        if (is_int($int))
        {
            return pack("NN", $int < 0 ? -1 : 0, $int);
        }

        if (function_exists("bcmul"))
        {
            if (bccomp($int, 0) == -1)
            {
                $int = bcadd("18446744073709551616", $int);
            }
            $h = bcdiv($int, "4294967296", 0);
            $l = bcmod($int, "4294967296");

            return pack("NN", (float)$h, (float)$l);
        }

        throw new \ErrorException("Unable to pack 64 bit integer value, extension BC Math not found.");
    }

    /**
     * Pack 64 integer (unsigned).
     *
     * @param int $int
     * @return string
     * @throws \ErrorException
     */
    protected function pack64u($int)
    {
        if (PHP_INT_SIZE >= 8)
        {
            if (is_int($int))
            {
                return pack("NN", $int >> 32, $int & 0xFFFFFFFF);
            }

            if (function_exists("bcmul"))
            {
                $h = bcdiv($int, 4294967296, 0);
                $l = bcmod($int, 4294967296);

                return pack("NN", $h, $l);
            }
        }

        if (is_int($int))
        {
            return pack("NN", 0, $int);
        }

        if (function_exists("bcmul"))
        {
            $h = bcdiv($int, "4294967296", 0);
            $l = bcmod($int, "4294967296");

            return pack("NN", (float)$h, (float)$l);
        }

        throw new \ErrorException("Unable to pack 64 bit integer value, extension BC Math not found.");
    }

    /**
     * Unpack 64 integer (signed).
     *
     * @param string $string
     * @return int
     * @throws \ErrorException
     */
    protected function unpack64($string)
    {
        list ($h, $l) = array_values(unpack("N*N*", $string));

        if (PHP_INT_SIZE >= 8)
        {
            if ($h < 0)
            {
                $h += (1 << 32);
            }

            if ($l < 0)
            {
                $l += (1 << 32);
            }

            return ($h << 32) + $l;
        }

        if ($h == 0)
        {
            if ($l > 0)
            {
                return $l;
            }

            return sprintf("%u", $l);
        }
        elseif ($h == -1)
        {
            if ($l < 0)
            {
                return $l;
            }

            return sprintf("%.0f", $l - 4294967296.0);
        }

        $neg = "";
        $c = 0;
        if ($h < 0)
        {
            $h = ~$h;
            $l = ~$l;
            $c = 1;
            $neg = "-";
        }

        $h = sprintf("%u", $h);
        $l = sprintf("%u", $l);

        if (function_exists("bcmul"))
        {
            return $neg . bcadd(bcadd($l, bcmul($h, "4294967296")), $c);
        }

        throw new \ErrorException("Unable to pack 64 bit integer value, extension BC Math not found.");
    }

    /**
     * Unpack 64 integer (unsigned).
     *
     * @param string $string
     * @return int
     * @throws \ErrorException
     */
    protected function unpack64u($string)
    {
        list ($h, $l) = array_values(unpack("N*N*", $string));

        if (PHP_INT_SIZE >= 8)
        {
            if ($h < 0)
            {
                $h += (1 << 32);
            }
            if ($l < 0)
            {
                $l += (1 << 32);
            }

            if ($h <= 2147483647)
            {
                return ($h << 32) + $l;
            }

            if (function_exists("bcmul"))
            {
                return bcadd($l, bcmul($h, "4294967296"));
            }
        }

        if ($h == 0)
        {
            if ($l > 0)
            {
                return $l;
            }

            return sprintf("%u", $l);
        }

        $h = sprintf("%u", $h);
        $l = sprintf("%u", $l);

        if (function_exists("bcmul"))
        {
            return bcadd($l, bcmul($h, "4294967296"));
        }

        throw new \ErrorException("Unable to pack 64 bit integer value, extension BC Math not found.");
    }

    /**
     * Negative to positive.
     *
     * @param int $number
     * @return int
     */
    protected function fix64u($number)
    {
        if (PHP_INT_SIZE >= 8)
        {
            if ($number < 0)
            {
                $number += (1 << 32);
            }

            return $number;
        }
        else
        {
            return sprintf("%u", $number);
        }
    }

    /**
     * Destructing.
     */
    public function __destruct()
    {
        if (is_resource($this->connection))
        {
            fclose($this->connection);
        }
    }
}