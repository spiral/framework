<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Translator;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Tests\BaseTest;

class IndexerTest extends BaseTest
{
    public function testIndex()
    {
        $this->app->translator->loadLocales();

        $this->assertSame(
            ConsoleDispatcher::CODE_UNDEFINED,
            $this->app->console->run('i18n:index')->getCode()
        );

        //todo: fix it
        print_r($this->app->translator->getCatalogue()->domainMessages('validation'));
    }
}