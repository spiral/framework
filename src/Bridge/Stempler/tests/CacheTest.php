<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Internal\Introspector;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Views\ViewContext;

class CacheTest extends BaseTestCase
{
    /** @var FilesInterface */
    protected $files;

    public function setUp(): void
    {
        parent::setUp();

        $this->files = new Files();

        /** @var ConfiguratorInterface $configurator */
        $configurator = $this->container->get(ConfiguratorInterface::class);
        $configurator->modify('views', new EnableCachePatch());
    }

    #[TestScope("http")]
    public function testCache(): void
    {
        self::assertCount(0, $this->files->getFiles(__DIR__ . '/cache/', '*.php'));

        $s = $this->getStempler();
        self::assertSame('test', $s->get('test', new ViewContext())->render([]));
        self::assertCount(2, $this->files->getFiles(__DIR__ . '/cache/', '*.php'));

        $s->reset('test', new ViewContext());
        self::assertCount(0, $this->files->getFiles(__DIR__ . '/../cache/', '*.php'));
    }
}
