<?php

declare(strict_types=1);

namespace Spiral\Prototype\Exception;

class ClassNotDeclaredException extends \Exception
{
    public function __construct(string $filename)
    {
        parent::__construct(\sprintf('Class declaration not found in "%s" directory.', $filename));
    }
}
