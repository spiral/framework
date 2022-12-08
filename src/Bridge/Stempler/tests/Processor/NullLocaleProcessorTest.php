<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Processor;

use Spiral\Tests\Stempler\BaseTest;
use Spiral\Views\ViewContext;

final class NullLocaleProcessorTest extends BaseTest
{
    public function testProcess(): void
    {
        $s = $this->getStempler();
        $this->assertSame(
            "Hello world!\n",
            $s->get('localized', new ViewContext())->render([])
        );
    }
}
