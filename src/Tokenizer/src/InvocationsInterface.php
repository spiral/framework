<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

use Spiral\Tokenizer\Reflection\ReflectionInvocation;

/**
 * Analog of LocatorInterface for method/function invocations. Can only work with simple invocations
 * such as $this->method, self::method, static::method, or ClassName::method.
 */
interface InvocationsInterface
{
    /**
     * Find all possible invocations of given function or method. Make sure you know about location
     * limitations.
     *
     * @return ReflectionInvocation[]
     */
    public function getInvocations(\ReflectionFunctionAbstract $function): array;
}
