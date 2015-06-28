<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http\Response;

use Psr\Http\Message\UriInterface;
use Spiral\Components\Http\Stream;
use Spiral\Components\Http\Response;

class Redirect extends Response
{
    /**
     * Redirect response.
     *
     * @param string|UriInterface $uri
     * @param int                 $status
     */
    public function __construct($uri, $status = self::REDIRECT)
    {
        parent::__construct(new Stream(), $status, [
            'Location' => (string)$uri
        ]);
    }
}