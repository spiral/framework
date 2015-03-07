<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\Http;

use Spiral\Core\Controller;

class HttpController extends Controller
{
    //    /**
    //     * If enabled exception information will be shared for client. If disabled client will receive 500 error without any
    //     * additional data. Exception snapshot and error logging performed by debug component and follows application rules.
    //     * Can be enabled by particular environments by listing their names in array.
    //     *
    //     * @var bool|array
    //     */
    //    protected $exposeErrors = array('development');
    //
    //    /**
    //     * Priority of options to be merged into options array, default values is only POST. Last options has highest priority
    //     * and always rewrite key created by previous options.
    //     *
    //     * Supported values: query, post, cookies
    //     *
    //     * @var array
    //     */
    //    protected $populateOptions = array('post');
    //
    //    /**
    //     * If false controller and action will be erased from request options to let this values be overwritten by query and
    //     * post data.
    //     *
    //     * @var bool
    //     */
    //    protected $actionOptions = true;
    //
    //    /**
    //     * Request instance will be used in controller, options array will be populated using POST and GET declared in
    //     * $controller->populateOptions property.
    //     *
    //     * @var null|Request
    //     */
    //    protected $request = null;
    //
    //    /**
    //     * Constructing controller. Will populate request options with POST and GET data if such options specified in
    //     * $controller->populateOptions.
    //     *
    //     * @param Request $request
    //     */
    //    public function __construct(Request $request = null)
    //    {
    //        if (!$this->actionOptions)
    //        {
    //            unset($request->options['controller'], $request->options['action']);
    //        }
    //
    //        foreach ($this->populateOptions as $option)
    //        {
    //            switch ($option)
    //            {
    //                case 'query':
    //                    $request->options = array_merge($request->query, $request->options);
    //                    break;
    //                case 'post':
    //                    $request->options = array_merge($request->post, $request->options);
    //                    break;
    //                case 'cookies':
    //                    $request->options = array_merge($request->cookies->getAll(), $request->options);
    //                    break;
    //            }
    //        }
    //
    //        $this->request = $request;
    //    }

    //    /**
    //     * This is canonical example of extending controller behaviour, call action will be redefined to format controller
    //     * responses using JSON response (arrays, strings, exceptions). However, if action will respond with already defined
    //     * response object that object will be forwarded to client.
    //     *
    //     * @param string $action
    //     * @return mixed
    //     */
    //    public function callAction($action = '')
    //    {
    //        try
    //        {
    //            $response = parent::callAction($action);
    //        }
    //        catch (ClientException $exception)
    //        {
    //            $httpCode = $exception->httpCode();
    //            $message = $exception->getMessage();
    //
    //            if (isset(HTTP::$statusMessages[$httpCode]))
    //            {
    //                if (!$this->exposeErrors || (is_array($this->exposeErrors) && !in_array($this->core->getEnvironment(), $this->exposeErrors)))
    //                {
    //                    $message = HTTP::$statusMessages[$httpCode];
    //                }
    //            }
    //
    //            return json(array('status' => (int)$httpCode, 'error' => $message));
    //        }
    //        catch (\Exception $exception)
    //        {
    //            if ((is_array($this->exposeErrors) && in_array($this->core->getEnvironment(), $this->exposeErrors)) || (is_bool($this->exposeErrors) && $this->exposeErrors))
    //            {
    //                return json(array('status' => 500, 'error' => $this->debug->handleException($exception)->getMessage()));
    //            }
    //
    //            return json(array('status' => 500, 'error' => HTTP::$statusMessages[500]));
    //        }
    //
    //        if (!$response instanceof Response)
    //        {
    //            return json($response);
    //        }
    //
    //        return $response;
    //    }
}