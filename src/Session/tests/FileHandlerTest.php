<?php

declare(strict_types=1);

namespace Spiral\Tests\Session;

use PHPUnit\Framework\TestCase;
use Spiral\Files\Files;
use Spiral\Session\Handler\FileHandler;

final class FileHandlerTest extends TestCase
{
    private string $directory;
    private FileHandler $handler;

    public function testCreateSid(): void
    {
        $id = $this->handler->create_sid();

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $id);
        self::assertNotSame($id, $this->handler->create_sid());
    }

    public function testValidateId(): void
    {
        self::assertFalse($this->handler->validateId('foo'));

        $this->handler->write('foo', 'bar');

        self::assertTrue($this->handler->validateId('foo'));
    }

    public function testUpdateTimestamp(): void
    {
        self::assertTrue($this->handler->updateTimestamp('foo', 'bar'));
        self::assertSame('bar', $this->handler->read('foo'));
    }

    protected function setUp(): void
    {
        $this->directory = \sys_get_temp_dir() . '/spiral_session_file_' . \getmypid() . '_' . \uniqid();
        $this->handler = new FileHandler(new Files(), $this->directory);
    }

    protected function tearDown(): void
    {
        if (\is_dir($this->directory)) {
            (new Files())->deleteDirectory($this->directory);
        }
    }
}
