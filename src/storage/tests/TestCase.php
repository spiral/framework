<?php

declare(strict_types=1);

namespace Spiral\Tests\Storage;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Spiral\Storage\Parser\UriParser;
use Spiral\Storage\Parser\UriParserInterface;

class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected const SERVER_NAME = 'debugServer';

    /**
     * @var string
     */
    protected const VFS_PREFIX = 'vfs://';

    /**
     * @var string
     */
    protected const ROOT_DIR = __DIR__ . '/storage/testRoot';

    /**
     * @var string
     */
    protected const CONFIG_HOST = 'http://localhost/debug/';

    /**
     * @var UriParserInterface|null
     */
    protected $uriParser;

    /**
     * @return UriParserInterface
     */
    protected function getUriParser(): UriParserInterface
    {
        if (!$this->uriParser instanceof UriParserInterface) {
            $this->uriParser = new UriParser();
        }

        return $this->uriParser;
    }

    /**
     * @param string $message
     */
    protected function notice(string $message): void
    {
        if (\method_exists($this, 'addWarning')) {
            /** @psalm-suppress InternalMethod */
            $this->addWarning($message);
        }
    }
}
