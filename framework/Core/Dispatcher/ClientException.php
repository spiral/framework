<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Dispatcher;

use Spiral\Core\CoreException;

class ClientException extends CoreException
{
    /**
     * Client exception codes will be handled by dispatcher according to working environment, such
     * exception should be used only when error caused by "soft" used error like wrong controller,
     * action or missed parameters. It can also be used to notify frontend about failed model
     * validation or invalid external API behaviour (500).
     */
    const BAD_REQUEST  = 400;
    const NOT_FOUND    = 404;
    const FORBIDDEN    = 403;
    const SERVER_ERROR = 500;

    /**
     * Due response code stored in exception message, errorCode() method can be used to retrieve
     * it via more recognizable way.
     *
     * @return int
     */
    public function errorCode()
    {
        if (!is_numeric($this->getMessage()))
        {
            return self::BAD_REQUEST;
        }

        return $this->getMessage();
    }
}