<?php

declare(strict_types=1);

namespace Spiral\SendIt\Renderer;

use Spiral\Mailer\Exception\MailerException;
use Spiral\Mailer\MessageInterface;
use Spiral\SendIt\RendererInterface;
use Spiral\Views\Exception\ViewException;
use Spiral\Views\ViewsInterface;
use Symfony\Component\Mime\Email;

final class ViewRenderer implements RendererInterface
{
    public function __construct(
        private readonly ViewsInterface $views
    ) {
    }

    public function render(MessageInterface $message): Email
    {
        try {
            $view = $this->views->get($message->getSubject());
        } catch (ViewException $e) {
            throw new MailerException(
                \sprintf('Invalid email template `%s`: %s', $message->getSubject(), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        $msg = new Email();

        if ($message->getFrom() !== null) {
            $msg->from($message->getFrom());
        }

        $msg->to(...$message->getTo());
        $msg->cc(...$message->getCC());
        $msg->bcc(...$message->getBCC());

        try {
            // render message partials
            $view->render(\array_merge(['_msg_' => $msg], $message->getData()));
        } catch (ViewException $e) {
            throw new MailerException(
                \sprintf('Unable to render email `%s`: %s', $message->getSubject(), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return $msg;
    }

    /**
     * Copy-pasted form https://stackoverflow.com/a/20806227
     * Make sure the subject is ASCII-clean
     *
     * @param string $subject Subject to encode
     * @return string Encoded subject
     */
    public static function escapeSubject(string $subject): string
    {
        if (!preg_match('/[^\x20-\x7e]/', $subject)) {
            // ascii-only subject, return as-is
            return $subject;
        }

        // Subject is non-ascii, needs encoding
        $encoded = \base64_encode($subject);
        $prefix = '=?UTF-8?B?';
        $suffix = '?=';

        return $prefix . \str_replace("=\r\n", $suffix . "\r\n  " . $prefix, $encoded) . $suffix;
    }
}
