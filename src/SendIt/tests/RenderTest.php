<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Mailer\Exception\MailerException;
use Spiral\Mailer\Message;
use Spiral\Mailer\MessageInterface;
use Spiral\SendIt\Bootloader\BuilderBootloader;
use Spiral\SendIt\Bootloader\MailerBootloader;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;
use Spiral\Testing\TestCase;
use Spiral\Tests\SendIt\App\MailInterceptorBootloader;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class RenderTest extends TestCase
{
    public static function strippedCommentsProvider(): iterable
    {
        yield 'single-line' => ['<p>a</p><!-- comment --><p>b</p>', '<p>a</p><p>b</p>'];
        yield 'multi-line' => ["<p>a</p><!--\n  <p>commented</p>\n--><p>b</p>", '<p>a</p><p>b</p>'];
        yield 'empty' => ['<p>a</p><!----><p>b</p>', '<p>a</p><p>b</p>'];
        yield 'dashes inside' => ['<p>a</p><!-- a - b -- c ---><p>b</p>', '<p>a</p><p>b</p>'];
        yield 'several' => ['<!-- 1 --><p>a</p><!-- 2 --><p>b</p><!-- 3 -->', '<p>a</p><p>b</p>'];
        yield 'inside conditional comment' => [
            '<!--[if mso]><table><!-- inner --></table><![endif]-->',
            '<!--[if mso]><table></table><![endif]-->',
        ];
        yield 'after raw-text element' => ['<style>.a{}</style><!-- comment --><p>a</p>', '<style>.a{}</style><p>a</p>'];
    }

    public static function keptHtmlProvider(): iterable
    {
        yield 'mso' => ['<!--[if mso]><b>outlook</b><![endif]-->'];
        yield 'mso version' => ['<!--[if gte mso 9]><b>outlook</b><![endif]-->'];
        yield 'mso uppercase' => ['<!--[IF MSO]><b>outlook</b><![ENDIF]-->'];
        yield 'not mso' => ['<!--[if !mso]><!--><div>not outlook</div><!--<![endif]-->'];
        yield 'not mso with empty comment' => ['<!--[if !mso]><!-- --><div>not outlook</div><!--<![endif]-->'];
        yield 'style' => ["<style type=\"text/css\"><!--\n.a { color: red; }\n--></style>"];
        yield 'style uppercase' => ['<STYLE><!-- .a { color: red; } --></STYLE>'];
        yield 'script' => ['<script type="application/ld+json">{"name": "<!-- not a comment -->"}</script>'];
        yield 'textarea' => ['<textarea><!-- not a comment --></textarea>'];
        yield 'title' => ['<title><!-- not a comment --></title>'];
        yield 'unclosed' => ['<p>a</p><!-- unclosed'];
        yield 'pcre backtrack limit' => ['<p>a</p><style>' . \str_repeat('<b>', 1_000_000) . '</style><!-- comment -->'];
    }

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

    #[DataProvider('strippedCommentsProvider')]
    public function testHtmlCommentsAreStripped(string $html, string $expected): void
    {
        self::assertSame($expected, $this->renderHtml($html));
    }

    #[DataProvider('keptHtmlProvider')]
    public function testHtmlIsKept(string $html): void
    {
        self::assertSame($html, $this->renderHtml($html));
    }

    public function testTemplateCommentsAreStrippedFromHtmlPartOnly(): void
    {
        $email = $this->send(new Message('comments', ['email@domain.com'], ['name' => 'Antony']));

        $html = $email->getHtmlBody();
        self::assertStringContainsString('<h1>Hello, Antony!</h1>', $html);
        self::assertStringNotContainsString('commented', $html);
        self::assertStringNotContainsString('multiline', $html);
        self::assertStringContainsString('<!--[if mso]><b>outlook</b><![endif]-->', $html);
        self::assertStringContainsString('<!--[if !mso]><!--><div>not outlook</div><!--<![endif]-->', $html);

        self::assertStringContainsString('<!-- not html -->', $email->getTextBody());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (\glob(__DIR__ . '/App/runtime/cache/views/*.php') as $file) {
            @\unlink($file);
        }
    }

    private function send(MessageInterface $message): Email
    {
        $this->getContainer()->get(MailJob::class)->handle(
            MailQueue::JOB_NAME,
            'id',
            \json_encode(MessageSerializer::pack($message)),
        );

        return $this->getContainer()->get(MailerInterface::class)->getLast();
    }

    private function renderHtml(string $html): string
    {
        return $this->send(new Message('raw-html', ['email@domain.com'], ['html' => $html]))->getHtmlBody();
    }
}
