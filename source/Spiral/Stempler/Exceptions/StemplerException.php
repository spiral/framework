<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Exceptions;

use Spiral\Core\Exceptions\ExceptionInterface;
use Spiral\Core\Exceptions\RuntimeException;

/**
 * StemplerException has ability to specify context token which will can used to define location
 * of html code caused error.
 */
class StemplerException extends RuntimeException implements ExceptionInterface
{
    /**
     * @var array
     */
    private $token = [];

    /**
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
     * @return array
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set exception location.
     *
     * @param string $file
     * @param string $line
     */
    public function setLocation($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }
}