<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Exceptions;

use Spiral\Stempler\Exceptions\LoaderExceptionInterface;

/**
 * Errors while view loading.
 */
class LoaderException extends \Twig_Error_Loader implements LoaderExceptionInterface
{
    //Sharing exception with Twig (why not?)
    //@todo check why not
}