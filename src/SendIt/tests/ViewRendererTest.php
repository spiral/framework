<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt;

use PHPUnit\Framework\TestCase;
use Spiral\Mailer\Message;
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
    /**
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
}
