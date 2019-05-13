<?php declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Views;

use Spiral\Framework\BaseTest;
use Spiral\Translator\Translator;
use Spiral\Views\ViewsInterface;

class LocaleTest extends BaseTest
{
    public function testRenderEn()
    {
        $app = $this->makeApp();

        $out = $app->get(ViewsInterface::class)->render('custom:locale');
        $this->assertSame('Hello English!', $out);
    }

    public function testRenderRu()
    {
        $app = $this->makeApp();
        $app->get(Translator::class)->setLocale('ru');

        $out = $app->get(ViewsInterface::class)->render('custom:locale');
        $this->assertSame('Hello Мир!', $out);
    }
}