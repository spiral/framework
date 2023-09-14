<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Config\ConfiguratorInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
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

    public function testCache(): void
    {
        $this->assertCount(0, $this->files->getFiles(__DIR__ . '/cache/', '*.php'));

        $s = $this->getStempler();
        $this->assertSame('test', $s->get('test', new ViewContext())->render([]));
        $this->assertCount(2, $this->files->getFiles(__DIR__ . '/cache/', '*.php'));

        $s->reset('test', new ViewContext());
        $this->assertCount(0, $this->files->getFiles(__DIR__ . '/../cache/', '*.php'));
    }
}
