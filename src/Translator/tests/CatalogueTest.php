<?php

declare(strict_types=1);

namespace Spiral\Tests\Translator;

use PHPUnit\Framework\TestCase;
use Spiral\Translator\Catalogue;
use Spiral\Translator\Exception\CatalogueException;
use Symfony\Component\Translation\MessageCatalogue;

class CatalogueTest extends TestCase
{
    public function testGetLocale(): void
    {
        $catalogue = new Catalogue('ru', []);

        self::assertSame('ru', $catalogue->getLocale());
        self::assertSame([], $catalogue->getData());
    }

    public function testHas(): void
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation',
            ],
            'views'    => [
                'view' => 'Russian View',
            ],
        ]);

        self::assertSame(['messages', 'views'], $catalogue->getDomains());

        self::assertTrue($catalogue->has('messages', 'message'));
        self::assertTrue($catalogue->has('views', 'view'));
        self::assertFalse($catalogue->has('messages', 'another-message'));
        self::assertFalse($catalogue->has('other-domain', 'message'));
    }

    public function testUndefinedString(): void
    {
        $this->expectExceptionMessage("Undefined string in domain 'domain'");
        $this->expectException(CatalogueException::class);

        $catalogue = new Catalogue('ru', []);
        $catalogue->get('domain', 'message');
    }

    public function testLoadAndGet(): void
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation',
            ],
            'views'    => [
                'view' => 'Russian View',
            ],
        ]);

        self::assertSame('Russian Translation', $catalogue->get('messages', 'message'));
        self::assertSame('Russian View', $catalogue->get('views', 'view'));
    }

    public function testLoadGetAndSet(): void
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation',
            ],
            'views'    => [
                'view' => 'Russian View',
            ],
        ]);

        self::assertSame('Russian Translation', $catalogue->get('messages', 'message'));
        self::assertSame('Russian View', $catalogue->get('views', 'view'));

        self::assertFalse($catalogue->has('views', 'message'));
        $catalogue->set('views', 'message', 'View Message');
        self::assertTrue($catalogue->has('views', 'message'));

        self::assertSame('View Message', $catalogue->get('views', 'message'));

        self::assertSame([
            'messages' => [
                'message' => 'Russian Translation',
            ],
            'views'    => [
                'view'    => 'Russian View',
                'message' => 'View Message',
            ],
        ], $catalogue->getData());
    }

    public function testMergeSymfonyAndFollow(): void
    {
        $catalogue = new Catalogue('ru', []);

        $catalogue->set('domain', 'message', 'Original Translation');
        self::assertSame('Original Translation', $catalogue->get('domain', 'message'));

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, true);

        self::assertSame('Original Translation', $catalogue->get('domain', 'message'));
    }

    public function testMergeSymfonyAndFollowOnEmpty(): void
    {
        $catalogue = new Catalogue('ru', []);

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, true);

        self::assertSame('Translation', $catalogue->get('domain', 'message'));
    }

    public function testMergeSymfonyAndReplace(): void
    {
        $catalogue = new Catalogue('ru', []);

        $catalogue->set('domain', 'message', 'Original Translation');
        self::assertSame('Original Translation', $catalogue->get('domain', 'message'));

        $messageCatalogue = new MessageCatalogue('ru', ['domain' => ['message' => 'Translation']]);
        $catalogue->mergeFrom($messageCatalogue, false);

        self::assertSame('Translation', $catalogue->get('domain', 'message'));
    }

    public function testToCatalogue(): void
    {
        $catalogue = new Catalogue('ru', [
            'messages' => [
                'message' => 'Russian Translation',
            ],
            'views'    => [
                'view' => 'Russian View',
            ],
        ]);

        $sc = $catalogue->toMessageCatalogue();

        self::assertSame('ru', $sc->getLocale());
        self::assertSame(['messages', 'views'], $sc->getDomains());
    }
}
