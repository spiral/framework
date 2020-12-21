<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Compiler\SourceMap;
use Spiral\Views\Exception\RenderException;

final class ExceptionMapper
{
    /** @var SourceMap */
    private $sourcemap;

    /** @var int */
    private $lineOffset;

    /**
     * @param SourceMap $sourcemap
     * @param int       $lineOffset
     */
    public function __construct(SourceMap $sourcemap, int $lineOffset)
    {
        $this->sourcemap = $sourcemap;
        $this->lineOffset = $lineOffset;
    }

    /**
     * @param \Throwable $e
     * @param string     $class
     * @param string     $filename
     * @param array      $data
     * @return \Throwable
     */
    public function mapException(\Throwable $e, string $class, string $filename, array $data): \Throwable
    {
        $line = $e->getLine();
        $classFilename = (new \ReflectionClass($class))->getFileName();

        // looking for proper view error location
        if ($e->getFile() !== $classFilename) {
            foreach ($e->getTrace() as $trace) {
                if (isset($trace['file']) && $trace['file'] === $classFilename) {
                    $line = $trace['line'];
                    break;
                }
            }
        }

        $userStack = [];
        foreach ($this->sourcemap->getStack($line - $this->lineOffset) as $stack) {
            $userStack[] = [
                'file'     => $stack['file'],
                'line'     => $stack['line'],
                'class'    => StemplerEngine::class,
                'type'     => '->',
                'function' => 'render',
                'args'     => [$data],
            ];

            if ($stack['file'] === $filename) {
                // no need to jump over root template
                break;
            }
        }


        $e = new RenderException($e);
        $e->setUserTrace($userStack);

        return $e;
    }
}
