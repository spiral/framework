<?php
/**
 *
 * This class used to override some header*() functions and http_response_code()
 *
 * We put these into the SapiEmitter namespace, so that SapiEmitter will use these versions of header*() and
 * http_response_code() when we test its output.
 */

declare(strict_types=1);

namespace Spiral\Tests\Http\SapiEmitter\Support;

/**
 * @source https://github.com/yiisoft/yii-web/blob/master/tests/Emitter/Support/HTTPFunctions.php
 * @license MIT
 * @copyright Yii Software LLC (http://www.yiisoft.com) All rights reserved.
 */
class HTTPFunctions
{
    /** @var string[][] */
    private static $headers = [];
    /** @var int */
    private static $responseCode = 200;
    private static $headersSent = false;
    private static string $headersSentFile = '';
    private static int $headersSentLine = 0;

    /**
     * Reset state
     */
    public static function reset(): void
    {
        self::$headers = [];
        self::$responseCode = 200;
        self::$headersSent = false;
        self::$headersSentFile = '';
        self::$headersSentLine = 0;
    }

    /**
     * Set header_sent() state
     */
    public static function set_headers_sent(bool $value = false, string $file = '', int $line = 0): void
    {
        static::$headersSent = $value;
        static::$headersSentFile = $file;
        static::$headersSentLine = $line;
    }

    /**
     * Check if headers have been sent
     */
    public static function headers_sent(&$file = null, &$line = null): bool
    {
        $file = static::$headersSentFile;
        $line = static::$headersSentLine;
        return static::$headersSent;
    }

    /**
     * Send a raw HTTP header
     */
    public static function header(string $string, bool $replace = true, ?int $http_response_code = null): void
    {
        if (strpos($string, 'HTTP/') !== 0) {
            $header = strtolower(explode(':', $string, 2)[0]);
            if ($replace || !array_key_exists($header, self::$headers)) {
                self::$headers[$header] = [];
            }
            self::$headers[$header][] = $string;
        }
        if ($http_response_code !== null) {
            self::$responseCode = $http_response_code;
        }
    }

    /**
     * Remove previously set headers
     */
    public static function header_remove(?string $header = null): void
    {
        if ($header === null) {
            self::$headers = [];
        } else {
            unset(self::$headers[strtolower($header)]);
        }
    }

    /**
     * Returns a list of response headers sent
     *
     * @return string[]
     */
    public static function headers_list(): array
    {
        $result = [];
        foreach (self::$headers as $values) {
            foreach ($values as $header) {
                $result[] = $header;
            }
        }
        return $result;
    }

    /**
     * Get or Set the HTTP response code
     */
    public static function http_response_code(?int $response_code = null): int
    {
        if ($response_code !== null) {
            self::$responseCode = $response_code;
        }
        return self::$responseCode;
    }

    /**
     * Check header is exists
     */
    public static function hasHeader(string $header): bool
    {
        return array_key_exists(strtolower($header), self::$headers);
    }
}
