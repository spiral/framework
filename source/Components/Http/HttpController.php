<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Psr\Http\Message\ResponseInterface;
use Spiral\Components\Http\Input\ActionRequest;
use Spiral\Core\Controller;
use Spiral\Core\Dispatcher\ClientException;

/**
 * HttpController will automatically validate all ActionRequest passed to controller action and
 * halt application in case of error. Meaning ActionRequest passed to action will always be valid.
 *
 * You can disable auto-validation on action level by adding doc comment "@internal-validation".
 */
abstract class HttpController extends Controller
{
    /**
     * Doc comment used to disable auto validation on action level.
     */
    const DISABLE_AUTO_VALIDATION = '@internal-validation';

    /**
     * Method executed before controller action being called. Should return nothing to let controller
     * execute action itself. Any returned result will prevent action execution and will be returned
     * from callAction.
     *
     * @param \ReflectionMethod $method    Method reflection.
     * @param array             $arguments Method arguments.
     * @return mixed
     */
    protected function beforeAction(\ReflectionMethod $method, array $arguments)
    {
        if (strpos($method->getDocComment(), static::DISABLE_AUTO_VALIDATION) !== false)
        {
            //Validation is disable
            return null;
        }

        foreach ($arguments as $argument)
        {
            if ($argument instanceof ActionRequest && !$argument->isValid())
            {
                //We can halt application
                return $this->wrapErrors($argument->getErrors());
            }
        }

        return null;
    }

    /**
     * Cast error response based on set of provided errors.
     *
     * @param array $errors
     * @return array|ResponseInterface
     * @throws ClientException
     */
    protected function wrapErrors(array $errors)
    {
        if ($this->input->isJsonExpected())
        {
            return [
                'status' => Response::BAD_REQUEST,
                'errors' => $errors
            ];
        }

        return new Response('', Response::BAD_REQUEST);
    }
}