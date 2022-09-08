<?php

declare(strict_types=1);

namespace Spiral\Distribution\Internal;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class AmazonUriFactory implements UriFactoryInterface
{
    /**
     * @var string
     */
    private const ERROR_NOT_AVAILABLE =
        'The "aws/aws-sdk-php" package is supplied with the Guzzle PSR-7 ' .
        'implementation, but it is not available. Please install the ' .
        '"aws/aws-sdk-php" package or use any other implementation of PSR-17 factories.'
    ;

    /**
     * AmazonUriFactory constructor.
     */
    public function __construct()
    {
        $this->assertAvailable();
    }

    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }

    private function assertAvailable(): void
    {
        if (\class_exists(Uri::class)) {
            return;
        }

        throw new \DomainException(self::ERROR_NOT_AVAILABLE);
    }
}
