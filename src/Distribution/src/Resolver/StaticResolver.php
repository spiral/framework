<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Distribution\Resolver;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class StaticResolver extends UriResolver
{
    /**
     * @var string
     */
    private const URI_PATH_DELIMITER = '/';

    /**
     * @var UriInterface
     */
    private $host;

    public function __construct(UriInterface $host)
    {
        $this->host = clone $host;
    }

    public static function fromFactory(UriFactoryInterface $factory, string $host): self
    {
        return new static($factory->createUri($host));
    }

    public static function create(string $host): self
    {
        return new static(new Uri($host));
    }

    /**
     * @param array<string, string> $query
     */
    public function resolve(string $file, array $query = []): UriInterface
    {
        return $this->host->withPath($this->suffix($file))
            ->withQuery(\http_build_query($query))
        ;
    }

    private function suffix(string $file): string
    {
        $prefix = \trim($this->host->getPath(), self::URI_PATH_DELIMITER);
        $file = \trim($file, self::URI_PATH_DELIMITER);

        return self::URI_PATH_DELIMITER . ('' === $prefix ? '' : $prefix . self::URI_PATH_DELIMITER) . $file;
    }
}
