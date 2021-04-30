<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Parser;

use Psr\Http\Message\UriInterface as PsrUriInterface;
use Spiral\Storage\Exception\UriException;

final class UriParser implements UriParserInterface
{
    /**
     * @var string
     */
    private const ERROR_INVALID_URI_TYPE =
        'URI argument must be a string-like ' .
        'or instance of one of [%s], but %s passed'
    ;

    /**
     * {@inheritDoc}
     */
    public function parse($uri): UriInterface
    {
        switch (true) {
            case $uri instanceof UriInterface:
                return $uri;

            case \is_string($uri):
            case $uri instanceof \Stringable:
                return $this->fromString((string)$uri);

            case $uri instanceof PsrUriInterface:
                return $this->fromPsrUri($uri);

            default:
                $message = \vsprintf(self::ERROR_INVALID_URI_TYPE, [
                    \implode(', ', [UriInterface::class, PsrUriInterface::class]),
                    \get_debug_type($uri)
                ]);

                throw new UriException($message);
        }
    }

    /**
     * @param PsrUriInterface $uri
     * @return UriInterface
     * @throws UriException
     */
    private function fromPsrUri(PsrUriInterface $uri): UriInterface
    {
        $path = \implode('/', [$uri->getHost(), $uri->getPath()]);

        return new Uri($uri->getScheme(), $path);
    }

    /**
     * @param string $uri
     * @return UriInterface
     * @throws UriException
     */
    private function fromString(string $uri): UriInterface
    {
        $chunks = \explode('://', $uri);

        return new Uri(\array_shift($chunks), \implode('/', $chunks));
    }
}
