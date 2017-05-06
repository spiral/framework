<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Http\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Http\MiddlewareInterface;
use Spiral\ORM\CommandInterface;
use Spiral\ORM\TransactionInterface;

class TransactionMiddleware implements TransactionInterface, MiddlewareInterface
{
    /**
     * @var TransactionInterface
     */
    private $transaction;

    /**
     * @var bool
     */
    private $enabled = true;

    /**
     * @param \Spiral\ORM\TransactionInterface $transaction
     * @param bool                             $enabled
     */
    public function __construct(TransactionInterface $transaction, bool $enabled = true)
    {
        $this->transaction = $transaction;
        $this->enabled = $enabled;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     * @param callable                                 $next
     *
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        try {
            return $next($request, $response);
        } finally {
            if ($this->enabled) {
                //By outer transaction
                $this->transaction->run();
            }
        }
    }

    /**
     * @param \Spiral\ORM\CommandInterface $command
     */
    public function addCommand(CommandInterface $command)
    {
        return $this->transaction->addCommand($command);
    }

    /**
     * Forwarded to outer tranasction.
     */
    public function run()
    {
        $this->transaction->run();
    }
}