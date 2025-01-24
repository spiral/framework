<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use Spiral\Mailer\MessageInterface;
use Spiral\SendIt\Bootloader\BuilderBootloader;
use Spiral\SendIt\Bootloader\MailerBootloader;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;
use Spiral\Testing\TestCase;
use Spiral\Mailer\Exception\MailerException;
use Spiral\Mailer\Message;
use Spiral\Tests\SendIt\App\MailInterceptorBootloader;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RenderTest extends TestCase
{
    public function defineBootloaders(): array
    {
        return [
            MailerBootloader::class,
            BuilderBootloader::class,
            MailInterceptorBootloader::class,
        ];
    }

    public function defineDirectories(string $root): array
    {
        return [
            'root' => __DIR__ . '/App',
            'app'  => __DIR__ . '/App',
        ] + parent::defineDirectories($root);
    }

    public function testRenderError(): void
    {
        $this->expectException(MailerException::class);
        $this->send(new Message('test', ['email@domain.com'], ['name' => 'Antony']));
    }

    public function testRender(): void
    {
        $email = $this->send(new Message('email', ['email@domain.com'], ['name' => 'Antony']));

        self::assertSame('Demo Email', $email->getSubject());

        $body = $email->getBody()->toString();
        self::assertStringContainsString('bootstrap.txt', $body);
        self::assertStringContainsString('<p>Hello, Antony!</p>', $body);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (glob(__DIR__ . '/App/runtime/cache/views/*.php') as $file) {
            @unlink($file);
        }
    }

    private function send(MessageInterface $message): Email
    {
        $this->getContainer()->get(MailJob::class)->handle(
            MailQueue::JOB_NAME,
            'id',
            json_encode(MessageSerializer::pack($message)),
        );

        return $this->getContainer()->get(MailerInterface::class)->getLast();
    }
}
