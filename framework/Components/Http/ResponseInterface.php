<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\ResponseInterface as PsrResponse;
use Spiral\Components\Http\Response\CookieInterface;

interface ResponseInterface extends PsrResponse
{
    /**
     * @return CookieInterface[]
     */
    public function getCookies();

    public function withCookie(CookieInterface $cookie);

    public function withCookies(array $cookies);
}