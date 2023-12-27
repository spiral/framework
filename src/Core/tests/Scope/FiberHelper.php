<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope;

use Fiber;
use Generator;

final class FiberHelper
{
    /**
     * @template TReturn
     *
     * @param callable(): TReturn $callable
     * @param null|callable(mixed $suspendedValue): void $check To check each suspended value.
     *
     * @return TReturn
     * @throws \Throwable
     */
    public static function runInFiber(callable $callable, ?callable $check = null): mixed
    {
        $fiber = new Fiber($callable);
        $value = $fiber->start();
        while (!$fiber->isTerminated()) {
            if ($check !== null) {
                try {
                    $value = $check($value);
                } catch (\Throwable $e) {
                    $value = $fiber->throw($e);
                    continue;
                }
            }
            $value = $fiber->resume($value);
        }
        return $fiber->getReturn();
    }

    /**
     * Runs a sequence of callables in fibers asynchronously.
     *
     * @param callable ...$callables
     *
     * @return array The results of each callable.
     */
    public static function runFiberSequence(callable ...$callables): array
    {
        /** @var array<array-key, Generator<int, mixed, mixed, mixed>> $fiberGenerators */
        $fiberGenerators = [];
        /** Values that were suspended by the fiber. */
        $suspends = [];
        $results = [];
        foreach ($callables as $key => $callable) {
            $fiberGenerators[$key] = (static function () use ($callable) {
                $fiber = new Fiber($callable);
                // Get ready
                yield null;

                $value = yield $fiber->start();
                while (!$fiber->isTerminated()) {
                    $value = yield $fiber->resume($value);
                }
                return $fiber->getReturn();
            })();
            $suspends[$key] = null;
            $results[$key] = null;
        }


        while ($fiberGenerators !== []) {
            foreach ($fiberGenerators as $key => $generator) {
                try {
                    $suspends[$key] = $generator->send($suspends[$key]);
                    if (!$generator->valid()) {
                        $results[$key] = $generator->getReturn();
                        unset($fiberGenerators[$key]);
                    }
                } catch (\Throwable $e) {
                    unset($fiberGenerators[$key]);
                    $results[$key] = $e;
                }
            }
        }

        return $results;
    }
}
