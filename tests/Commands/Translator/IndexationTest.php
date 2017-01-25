<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Commands\Translator;

use Spiral\Tests\BaseTest;

class IndexationTest extends BaseTest
{
    public function testIndexMessages()
    {
        $this->assertSame(0, $this->app->console->run('i18n:index')->getCode());
        $this->assertNotEmpty(
            $this->app->translator->getCatalogue()->domainMessages('validation')
        );
    }

    public function testIndexSay()
    {
        $this->assertSame(0, $this->app->console->run('i18n:index')->getCode());

        //In controller
        $this->assertArrayHasKey(
            'Hello world',
            $this->app->translator->getCatalogue()->domainMessages('messages')
        );
    }
}