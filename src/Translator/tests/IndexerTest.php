<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\ClassLocator;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\InvocationLocator;
use Spiral\Tokenizer\InvocationsInterface;
use Spiral\Tokenizer\ScopedClassesInterface;
use Spiral\Tokenizer\ScopedClassLocator;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Config\TranslatorConfig;
use Spiral\Translator\Indexer;
use Spiral\Translator\Traits\TranslatorTrait;

class IndexerTest extends TestCase
{
    use TranslatorTrait;

    public const MESSAGES = [
        '[[indexer-message]]',
        'not-message'
    ];

    public function testIndexShortFunctions(): void
    {
        $catalogue = new Catalogue('en');
        $indexer = new Indexer(new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]), $catalogue);

        $indexer->indexInvocations($this->tContainer()->get(InvocationsInterface::class));

        $this->assertTrue($catalogue->has('messages', 'hello'));
        $this->assertTrue($catalogue->has('messages', '{n} dog|{n} dogs'));

        $this->assertTrue($catalogue->has('spiral', 'other'));
        $this->assertTrue($catalogue->has('spiral', 'hi-from-class'));
    }

    public function testIndexClasses(): void
    {
        $catalogue = new Catalogue('en');
        $indexer = new Indexer(new TranslatorConfig([
            'domains' => [
                'spiral'   => [
                    'spiral-*'
                ],
                'messages' => ['*']
            ]
        ]), $catalogue);

        $indexer->indexClasses($this->tContainer()->get(ScopedClassesInterface::class));

        $this->assertTrue($catalogue->has('spiral', 'indexer-message'));
        $this->assertFalse($catalogue->has('spiral', 'not-message'));

        // from stubs
        $this->assertTrue($catalogue->has('spiral', 'some-text'));
        $this->assertFalse($catalogue->has('spiral', 'no-message'));

        $this->assertTrue($catalogue->has('spiral', 'new-mess'));
    }

    protected function tContainer(): Container
    {
        $container = new Container();
        $container->bind(ScopedClassesInterface::class, ScopedClassLocator::class);
        $container->bind(InvocationsInterface::class, InvocationLocator::class);

        $container->bind(TokenizerConfig::class, new TokenizerConfig([
            'directories' => [__DIR__],
            'exclude'     => [],
            'scopes' => [
                'translations' => [
                    'directories' => [__DIR__],
                ]
            ]
        ]));

        return $container;
    }

    private function inner(): void
    {
        $var = 'something';
        l($var);

        l('other', [], 'spiral-domain');
        l('hello');
        p('{n} dog|{n} dogs', 1);
        p('{n} cat|{n} cats', 1, [], 'spiral-domain');
    }
}
