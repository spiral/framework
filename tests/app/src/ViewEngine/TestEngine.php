<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\App\ViewEngine;

use Spiral\Views\ContextInterface;
use Spiral\Views\Engine\AbstractEngine;
use Spiral\Views\Exception\EngineException;
use Spiral\Views\ViewInterface;

class TestEngine extends AbstractEngine
{
    protected const EXTENSION = 'custom';

    public function compile(string $path, ContextInterface $context)
    {
        if ($path == 'custom:error') {
            throw new EngineException("Unable to compile custom:error");
        }
    }

    public function reset(string $path, ContextInterface $context)
    {

    }

    public function get(string $path, ContextInterface $context): ViewInterface
    {

    }
}