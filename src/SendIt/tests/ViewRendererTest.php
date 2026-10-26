<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Mailer\Message;
use Spiral\Mailer\MessageInterface;
use Spiral\SendIt\Event\PostRender;
use Spiral\SendIt\Event\PreRender;
use Spiral\SendIt\Renderer\ViewRenderer;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewsInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @coversDefaultClass \Spiral\SendIt\Renderer\ViewRenderer
 */
final class ViewRendererTest extends TestCase
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
        yield 'empty' => [''];
        yield 'plain' => ['<p>Hello, <b>Antony</b>!</p>'];
        yield 'dashes and arrows in text' => ['<p>a -- b -> c --> d</p>'];
        yield 'multibyte' => ['<p>Привет, 世界 👋</p>'];
        yield 'email document' => [<<<'HTML'
            <!DOCTYPE html>
            <html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
            <head>
                <meta charset="utf-8">
                <title>Welcome</title>
                <style>
                    .button { background: #000; }
                    @media (max-width: 600px) { .container { width: 100% !important; } }
                </style>
                <!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
            </head>
            <body>
                <table role="presentation" class="container" width="600">
                    <tr><td align="center"><a class="button" href="https://example.com/?a=1&amp;b=2">Confirm</a></td></tr>
                </table>
            </body>
            </html>
            HTML];
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

    #[DataProvider('strippedCommentsProvider')]
    public function testStripHtmlComments(string $html, string $expected): void
    {
        self::assertSame($expected, ViewRenderer::stripHtmlComments($html));
    }

    #[DataProvider('keptHtmlProvider')]
    public function testStripHtmlCommentsKeepsHtml(string $html): void
    {
        self::assertSame($html, ViewRenderer::stripHtmlComments($html));
    }

    /**
     * Checking for event hits and processing by listeners without changing the original $message
     *
     * @covers ::render
     */
    public function testRender(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects(self::once())->method('render');

        $views = $this->createMock(ViewsInterface::class);
        $views->expects(self::once())->method('get')->willReturn($view);

        $message = new Message('subject has not been changed', 'to@mail.test');
        $beforeHash = \spl_object_hash($message);

        $eventDispatcher = new TraceableEventDispatcher(
            new EventDispatcher(),
            new Stopwatch(),
        );

        $eventDispatcher->addListener(PreRender::class, static function (PreRender $event): void {
            $message = $event->message;
            $message->setSubject('subject1');
        });
        $eventDispatcher->addListener(PostRender::class, static function (PostRender $event): void {
            $event->message->setSubject('subject2');
        });

        $target = new ViewRenderer($views, $eventDispatcher);
        $msg = $target->render($message);

        $afterHash = \spl_object_hash($message);

        self::assertSame($beforeHash, $afterHash);
        self::assertSame(['To: to@mail.test'], $msg->getHeaders()->toArray());
        self::assertCount(2, $eventDispatcher->getCalledListeners());
        self::assertSame('subject has not been changed', $message->getSubject());
    }

    /**
     * Health check when EventDispatcher = null.
     *
     * @covers ::render
     */
    public function testRenderWithoutDispatcher(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects(self::once())->method('render');

        $views = $this->createMock(ViewsInterface::class);
        $views->expects(self::once())->method('get')->willReturn($view);

        $message = new Message('subject has not been changed', 'to@mail.test');

        $target = new ViewRenderer($views);
        $target->render($message);
    }

    /**
     * Check that all added headers are added to the final object.
     *
     * @covers ::render
     */
    public function testRenderCheckBaseHeaders(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects(self::once())->method('render');

        $views = $this->createMock(ViewsInterface::class);
        $views->expects(self::once())->method('get')->willReturn($view);

        $message = $this->createMock(MessageInterface::class);
        $message
            ->expects(self::exactly(2))
            ->method('getFrom')
            ->willReturn('from@fortest.com');
        $message
            ->expects(self::exactly(2))
            ->method('getReplyTo')
            ->willReturn('reply-to@fortest.com');
        $message
            ->expects(self::exactly(1))
            ->method('getTo')
            ->willReturn(['to@fortest.com']);
        $message
            ->expects(self::exactly(1))
            ->method('getCC')
            ->willReturn(['cc@fortest.com']);
        $message
            ->expects(self::exactly(1))
            ->method('getBCC')
            ->willReturn(['bcc@fortest.com']);

        $target = new ViewRenderer($views);
        $msg = $target->render($message);

        self::assertSame([
            'From: from@fortest.com',
            'Reply-To: reply-to@fortest.com',
            'To: to@fortest.com',
            'Cc: cc@fortest.com',
            'Bcc: bcc@fortest.com',
        ], $msg->getHeaders()->toArray());
    }
}
