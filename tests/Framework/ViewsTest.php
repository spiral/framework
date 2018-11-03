<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\Views\ViewsInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ViewsTest extends BaseTest
{
    public function testRenderViewWithTranslator()
    {
        $app = $this->makeApp();

        /** @var ViewsInterface $views */
        $views = $app->get(ViewsInterface::class);

        $this->assertSame('Hello, English!', $views->render('home'));
    }

    public function testRenderViewWithAnotherLanguage()
    {
        $app = $this->makeApp();

        /** @var ViewsInterface $views */
        $views = $app->get(ViewsInterface::class);

        $this->assertSame('Hello, English!', $views->render('home'));

        $app->get(TranslatorInterface::class)->setLocale('ru');
        $this->assertSame('Hello, Мир!', $views->render('home'));

        $app->get(TranslatorInterface::class)->setLocale('en');
        $this->assertSame('Hello, English!', $views->render('home'));
    }
}