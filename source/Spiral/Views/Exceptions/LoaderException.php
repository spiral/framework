<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Exceptions;

use Spiral\Core\Exceptions\ExceptionInterface;

/**
 * Errors while view loading.
 */
class LoaderException extends \Twig_Error_Loader implements ExceptionInterface
{

}