<?php

declare(strict_types=1);

namespace Spiral\Http\Emitter;

use Spiral\Tests\Http\SapiEmitter\Support\HTTPFunctions;

if (!function_exists(__NAMESPACE__ . '\\headers_sent')) {
    /**
     * Mock for the headers_sent() function for Emitter class.
     */
    function headers_sent(&$file = null, &$line = null): bool
    {
        return HTTPFunctions::headers_sent($file, $line);
    }

    /**
     * Mock for the header() function for Emitter class.
     */
    function header(string $string, bool $replace = true, ?int $http_response_code = null): void
    {
        HTTPFunctions::header($string, $replace, $http_response_code);
    }

    /**
     * Mock for the header_remove() function for Emitter class.
     */
    function header_remove(): void
    {
        HTTPFunctions::header_remove();
    }

    /**
     * Mock for the header_list() function for Emitter class.
     */
    function header_list(): array
    {
        return HTTPFunctions::headers_list();
    }

    /**
     * Mock for the http_response_code() function for Emitter class.
     */
    function http_response_code(?int $response_code = null): int
    {
        return HTTPFunctions::http_response_code($response_code);
    }
}
