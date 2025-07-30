<?php

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\TestCase;
use Spiral\Mailer\MessageInterface;
use Spiral\SendIt\Renderer\ViewRenderer;
use Spiral\Views\ViewInterface;
use Spiral\Views\ViewsInterface;

/**
 * @coversDefaultClass \Spiral\SendIt\Renderer\ViewRenderer
 */
final class ViewRendererTest extends TestCase
{
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
