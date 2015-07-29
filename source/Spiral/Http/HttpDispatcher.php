<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Http;

use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Http\Responses\HtmlResponse;
use Spiral\Http\Responses\JsonResponse;

class HttpDispatcher extends HttpCore implements DispatcherInterface
{
    /**
     * Every dispatcher should know how to handle exception snapshot provided by spiral core.
     *
     * @param SnapshotInterface $snapshot
     * @return mixed
     */
    public function handleSnapshot(SnapshotInterface $snapshot)
    {
        if (!$this->config['exposeErrors'])
        {
            $this->handleException($snapshot->getException());

            return;
        }

        if ($this->request->getHeaderLine('Accept') == 'application/json')
        {
            $context = ['status' => Response::SERVER_ERROR] + $snapshot->describe();
            $response = new JsonResponse($context, Response::SERVER_ERROR);
        }
        else
        {
            $response = new HtmlResponse($snapshot->render(), Response::SERVER_ERROR);
        }

        $this->dispatch($response);
    }
}