<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Helpers;

class HttpHelper
{
    /**
     * Contains the last response error.
     *
     * @var string
     */
    protected static $lastError = null;

    /**
     * List of last response header. This will be overwritten with each new query.
     *
     * @var array
     */
    protected static $lastHeaders = array();

    /**
     * Performs a simple HTTP GET query to specified URL. Query will be performed using sockets context so it can work
     * even if no CURL extension is available. Make sure that the HTTPS queries can be performed only if SSL support is
     * available.
     *
     * @param string $url     Destination to get content from. This can include port and protocol.
     * @param array  $query   Array of GET parameters (key => value) will be encoded and sent to addition to URL.
     * @param array  $cookies List of cookies to include (key => value).
     * @return string|bool
     */
    public static function get($url, array $query = array(), array $cookies = array())
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Cookie: ' . str_replace('&', ';', http_build_query($cookies))
                )
            ));

        try
        {
            $response = file_get_contents(trim($url . '?' . http_build_query($query), '?'), false, $context);
            self::$lastHeaders = isset($http_response_header) ? $http_response_header : array();

            return $response;
        }
        catch (\Exception $exception)
        {
            self::$lastError = $exception->getMessage();
            self::$lastHeaders = isset($http_response_header) ? $http_response_header : array();

            //Http error
            return false;
        }
    }

    /**
     * Performs simple HTTP POST query to a specified URL. Query is be performed using the sockets context so it will work
     * even if there is no CURL extension available. Make sure that HTTPS queries can be performed if SSL support available.
     *
     * @param string $url     Destination to get content from. Can include port and protocol.
     * @param array  $data    Array of POST parameters (key => value) will be encoded and added to the URL.
     * @param array  $cookies List of cookies to include (key => value).
     * @return string|bool
     */
    public static function post($url, array $data = array(), array $cookies = array())
    {
        $cookies = 'Cookie: ' . str_replace('&', ';', http_build_query($cookies));
        $context = stream_context_create(
            array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL . $cookies,
                    'content' => http_build_query($data)
                )
            ));

        try
        {
            $response = file_get_contents($url, false, $context);
            self::$lastHeaders = isset($http_response_header) ? $http_response_header : array();

            return $response;
        }
        catch (\Exception $exception)
        {
            self::$lastError = $exception->getMessage();
            self::$lastHeaders = isset($http_response_header) ? $http_response_header : array();

            //Http error
            return false;
        }
    }

    /**
     * Last HTTP response headers. In some cases this can be empty.
     *
     * @return null|array
     */
    public static function lastHeaders()
    {
        return self::$lastHeaders;
    }

    /**
     * Last HTTP request error (will contain an exception message).
     *
     * @return null|string
     */
    public static function lastError()
    {
        return self::$lastError;
    }
}