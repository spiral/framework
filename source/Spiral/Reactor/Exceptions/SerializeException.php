<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Reactor\Exceptions;

use Spiral\Core\Exceptions\ExceptionInterface;

/**
 * Error while serialization.
 */
class SerializeException extends \LogicException implements ExceptionInterface
{

}