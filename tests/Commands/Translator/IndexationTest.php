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

        //In controller
        $this->assertArrayHasKey(
            'Hello world',
            $this->app->translator->getCatalogue()->domainMessages('external')
        );
    }

    public function testIndexL()
    {
        $this->assertSame(0, $this->app->console->run('i18n:index')->getCode());

        $this->assertArrayHasKey(
            'l',
            $this->app->translator->getCatalogue()->domainMessages('messages')
        );

        $this->assertArrayHasKey(
            'l',
            $this->app->translator->getCatalogue()->domainMessages('external')
        );
    }

    public function testIndexP()
    {
        $this->assertSame(0, $this->app->console->run('i18n:index')->getCode());

        //In controller
        $this->assertArrayHasKey(
            '%s unit|%s units',
            $this->app->translator->getCatalogue()->domainMessages('messages')
        );

        $this->assertArrayHasKey(
            '%s unit|%s units',
            $this->app->translator->getCatalogue()->domainMessages('external')
        );
    }
}