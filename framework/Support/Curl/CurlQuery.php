<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Curl;

use Spiral\Core\Component;

class CurlQuery extends Component
{
    /**
     * Logging CURL queries.
     */
    use Component\LoggerTrait;

    /**
     * Log message format.
     */
    const LOG_FORMAT = 'URL: {url} ({http_code}), connection: {connect_time} s, start-transfer: {starttransfer_time} s, dns lookup: {namelookup_time} s, total: {total_time} s.';

    /**
     * HTTP method will be used to perform CURL request.
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * URL will be requested.
     *
     * @var string
     */
    protected $url = '';

    /**
     * Headers will be sent to CURL request, associated array name=>value.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Array of GET data will be attached to CURL request, associated array of name=>value. GET will be encoded using
     * http_build_query().
     *
     * @var array
     */
    protected $getData = array();

    /**
     * Array of POST fields, will be sent directly to CURL in array or string form. To set POST as string use setRawPOST()
     * class method, in this case no data will be additionally encoded.
     *
     * @var array|string
     */
    protected $postData = array();

    /**
     * List of headers parsed from response, every header represent as key=>value association.
     *
     * @var array
     */
    protected $responseHeaders = array();

    /**
     * Cookie names and values fetched from CURL response headers.
     *
     * @var array
     */
    protected $responseCookies = array();

    /**
     * HTTP status returned by CURL request.
     *
     * @var mixed
     */
    protected $httpStatus = null;

    /**
     * Error message of failed CURL request.
     *
     * @var string
     */
    public $curlError = null;

    /**
     * New CurlQuery class, can be used to perform various requests to external api and websites, CurlQuery class can be extended
     * to support additional syntax, response types or define custom behaviour. Https requests can only be made if they are
     * supported by server environment. All requests will be made using CURL extension.
     *
     * @param string $url    URL has to be requested.
     * @param string $method HTTP method to be used.
     * @throws CurlException
     */
    public function __construct($url, $method = 'GET')
    {
        if (!extension_loaded('curl'))
        {
            throw new CurlException('Unable to create CurlQuery, curl extension required.');
        }

        $this->url = $url;
        $this->method = $method;
    }

    /**
     * Add or replace request header by name and value.
     *
     * @param string $header Header name.
     * @param string $value  Header value.
     * @return static
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * Add or set GET value.
     *
     * @param string $name GET name.
     * @param string $value
     * @return static
     */
    public function setGET($name, $value)
    {
        $this->getData[$name] = $value;

        return $this;
    }

    /**
     * Add or set POST value.
     *
     * @param string $name GET name.
     * @param string $value
     * @return static
     */
    public function setPOST($name, $value)
    {
        $this->postData[$name] = $value;

        return $this;
    }

    /**
     * Write data directly to POST request, can be either in array form (will be encoded) or string (binary).
     *
     * @param mixed $postData POST in array or string format.
     * @return static
     */
    public function setRawPOST($postData)
    {
        $this->postData = $postData;

        return $this;
    }

    /**
     * Generate list of headers to send to CURL request, can be extended to perform additional headers logic, for example
     * signatures or dynamic timestamps.
     *
     * @return array
     */
    protected function buildHeaders()
    {
        $result = array();
        foreach ($this->headers as $header => $value)
        {
            if ($value !== '')
            {
                $result[] = $header . ': ' . $value;
            }
        }

        return $result;
    }

    /**
     * Compile GET data to url encoded string which will be attached to query URL.
     *
     * @return string
     */
    protected function buildGET()
    {
        if (!$this->getData)
        {
            return false;
        }

        return '?' . http_build_query($this->getData);
    }

    /**
     * Custom method which can be extended by CurlQuery children to implement custom CURL setup logic. This method will
     * be called before CURL request made, but after POST and GET data were set. Headers will be set after this method.
     *
     * @param mixed $curl
     */
    protected function prepareCURL($curl)
    {
        //Put your code here
    }

    /**
     * Custom method which can be extended by CurlQuery children to implement custom CURL response parsing logic. Will
     * be called after CURL request made, but only if request succeeded.
     *
     * @param mixed $result
     * @return mixed
     */
    protected function processResult($result)
    {
        return $result;
    }

    /**
     * Perform CURL query and return response body or parsed data (if processResult() implemented). This method build CURL
     * query using specified GET, POST and headers data, to specify custom CURL query setup logic redefine prepareCURL()
     * method.
     *
     * @return mixed
     * @throws CurlException
     */
    public function run()
    {
        $curl = curl_init($this->url . $this->buildGET());

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, 'parseHeaders'));

        if ($this->postData)
        {
            is_array($this->postData) && curl_setopt($curl, CURLOPT_POST, count($this->postData));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->postData);
        }

        switch ($this->method)
        {
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'HEAD':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
                break;
        }

        $this->prepareCURL($curl);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->buildHeaders());
        curl_setopt($curl, CURLOPT_HEADER, false);

        benchmark('curl', $this->url);
        if (!$result = curl_exec($curl))
        {
            benchmark('curl', $this->url);
            if ($this->curlError = curl_errno($curl))
            {
                $info = curl_getinfo($curl);
                $this->logger()->error(static::LOG_FORMAT, $info);

                //Connection, protocols or etc errors
                throw new CurlException(curl_error($curl));
            }
        }
        benchmark('curl', $this->url);

        $info = curl_getinfo($curl);
        $this->logger()->info(static::LOG_FORMAT, $info);
        curl_close($curl);

        switch ($this->getResponseHeader('content-encoding'))
        {
            case 'gzip':
                $result = gzdecode($result);
                break;
            case 'deflate':
                $result = gzinflate($result);
                break;
        }

        return $this->processResult($result);
    }

    /**
     * Response status code.
     *
     * @return mixed|null
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * Get response header value or return null. Header name will be lowercased while fetching from the list, this may create
     * potential problem when response contain similar headers different only by letter case.
     *
     * @param string $header
     * @return mixed
     */
    public function getResponseHeader($header)
    {
        $header = strtolower($header);

        return isset($this->responseHeaders[$header]) ? $this->responseHeaders[$header] : null;
    }

    /**
     * List of all response headers, every header name will be lowercased.
     *
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * List of cookie names and values parsed from response headers.
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->responseCookies;
    }

    /**
     * Will parse all headers and chunk them from response body.
     *
     * @param resource $curl   CURL resource.
     * @param string   $header Header to be parsed.
     * @return int
     */
    public function parseHeaders(&$curl, $header)
    {
        if (preg_match('/^HTTP\/1\.[01] (\d{3}) .*/i', $header, $matches))
        {
            $this->httpStatus = $matches[1];
        }
        elseif ($header != "\r\n")
        {
            if (strpos($header, ':'))
            {
                list($name, $content) = explode(':', $header, 2);
                $this->responseHeaders[strtolower($name)] = trim(trim($content), '"');

                if (strtolower($name) == 'set-cookie')
                {
                    $cookieContent = trim(trim($content), '"');
                    $cookieContent = explode(';', $cookieContent);

                    foreach ($cookieContent as $element)
                    {
                        $divider = strpos($element, '=');
                        $name = substr($element, 0, $divider);
                        $content = substr($element, $divider + 1);

                        $this->responseCookies[$name] = trim($content, '" ');
                        break;
                    }
                }
            }
        }

        return strlen($header);
    }
}