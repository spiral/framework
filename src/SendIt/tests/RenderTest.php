<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\TestCase;
use Spiral\Tests\SendIt\App\App;
use Spiral\Mailer\Exception\MailerException;
use Spiral\Mailer\Message;

/**
 * @requires function \Spiral\Framework\Kernel::create
 */
class RenderTest extends TestCase
{
    private $app;

    public function setUp(): void
    {
        $this->app = App::create([
            'root' => __DIR__ . '/App',
            'app'  => __DIR__ . '/App'
        ])->run();
    }

    public function tearDown(): void
    {
        foreach (glob(__DIR__ . '/App/runtime/cache/views/*.php') as $file) {
            @unlink($file);
        }
    }

    public function testRenderError(): void
    {
        $this->expectException(MailerException::class);
        $this->app->send(new Message('test', ['email@domain.com'], ['name' => 'Antony']));
    }

    public function testRender(): void
    {
        $email = $this->app->send(new Message('email', ['email@domain.com'], ['name' => 'Antony']));

        $this->assertSame('Demo Email', $email->getSubject());

        $body = $email->getBody()->toString();
        $this->assertStringContainsString('bootstrap.txt', $body);
        $this->assertStringContainsString('<p>Hello, Antony!</p>', $body);
    }
}
