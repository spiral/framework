<?php

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Compiler\SourceMap;
use Spiral\Views\Exception\RenderException;

final class ExceptionMapper
{
    public function __construct(
        private readonly SourceMap $sourcemap,
        private readonly int $lineOffset
    ) {
    }

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
