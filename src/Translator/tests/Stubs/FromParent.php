<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator\Stubs;

class FromParent extends MessageStub
{
    private $other = [
        '[[new-mess]]',
    ];

    protected function hi()
    {
        return $this->say('hi-from-class');
    }
}
