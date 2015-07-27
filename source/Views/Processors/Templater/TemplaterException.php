<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater;

use Spiral\Components\View\ViewException;

class TemplaterException extends ViewException
{
    /**
     * Token context.
     *
     * @var array
     */
    protected $token = [];

    /**
     * TemplaterException has ability to specify context token which will be used to define
     * location of html code caused error.
     *
     * @param string     $message
     * @param array      $token
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($message, array $token = [], $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->token = $token;
    }

    /**
     * Get token context.
     *
     * @return array
     */
    public function getToken()
    {
        return $this->token;
    }
}