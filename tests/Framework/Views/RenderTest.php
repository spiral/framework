<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Views;

use Spiral\Tests\Framework\BaseTest;
use Spiral\Views\ViewsInterface;

class RenderTest extends BaseTest
{
    public function testRenderWithNullVariable(): void
    {
        $app = $this->makeApp();

        $out = $app->get(ViewsInterface::class)->render('stempler:null', ['var' => null]);

        // any exceptions threw
        $this->assertIsString($out);
    }
}
