<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Http;

/**
 * {@inheritdoc}
 */
class Response extends \Zend\Diactoros\Response
{
    /**
     * Common set of http codes.
     */
    const SUCCESS           = 200;
    const CREATED           = 201;
    const ACCEPTED          = 202;
    const BAD_REQUEST       = 400;
    const UNAUTHORIZED      = 401;
    const FORBIDDEN         = 403;
    const NOT_FOUND         = 404;
    const SERVER_ERROR      = 500;
    const REDIRECT          = 307;
    const MOVED_PERMANENTLY = 301;
}