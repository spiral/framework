<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

/**
 * @internal
 */
trait DestructorTrait
{
    public function destruct(): void
    {
        foreach ($this as $var => $value) {
            unset($this->$var);
            if (\is_object($value) && \method_exists($value, 'destruct')) {
                $value->destruct();
            }
        }
    }
}
