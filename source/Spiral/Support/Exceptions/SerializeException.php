<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Support\Exceptions;

use Spiral\Core\Exceptions\ExceptionInterface;

/**
 * Error while serialization.
 */
class SerializeException extends \LogicException implements ExceptionInterface
{

}