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
use Spiral\Translator\TranslatorInterface;
use Spiral\Translator\Views\LocaleDependency;
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

        $app->getContainer()->runScope([
            TranslatorInterface::class => $app->get(Translator::class)->withLocale('ru')
        ], function () use ($app) {
            $out = $app->get(ViewsInterface::class)->render('custom:locale');
            $this->assertSame('Hello Мир!', $out);
        });
    }

    public function testLocaleDependency()
    {
        $app = $this->makeApp();
        $d = $app->get(LocaleDependency::class);

        $d = $d->__debugInfo();

        $this->assertSame('en', $d['value']);

        $this->assertTrue(in_array('en', $d['variants']));
        $this->assertTrue(in_array('ru', $d['variants']));
    }
}