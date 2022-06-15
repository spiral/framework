<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Views;

use Spiral\Tests\Framework\BaseTest;
use Spiral\Views\ViewsInterface;

final class RenderTest extends BaseTest
{
    public function testWithNullVariable(): void
    {
        $out = $this->getContainer()->get(ViewsInterface::class)
            ->render('stempler:null', ['var' => null, 'users' => []]);

        // any exceptions threw
        $this->assertIsString($out);
    }

    public function testWithNullVariableExression(): void
    {
        $out = $this->getContainer()->get(ViewsInterface::class)->render(
            'stempler:null',
            ['var' => null, 'users' => ['foo']]
        );

        // any exceptions threw
        $this->assertIsString($out);
    }
}
