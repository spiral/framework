<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt\App;

use Spiral\Framework\Kernel;
use Spiral\Mailer\MessageInterface;
use Spiral\SendIt\Bootloader\BuilderBootloader;
use Spiral\SendIt\Bootloader\MailerBootloader;
use Spiral\SendIt\MailJob;
use Spiral\SendIt\MailQueue;
use Spiral\SendIt\MessageSerializer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class App extends Kernel
{
    protected const LOAD = [
        MailerBootloader::class,
        BuilderBootloader::class,
        MailInterceptorBootloader::class
    ];

    /**
     * @param MessageInterface $message
     * @return Email
     * @throws \Throwable
     */
    public function send(MessageInterface $message): Email
    {
        $this->container->get(MailJob::class)->handle(
            MailQueue::JOB_NAME,
            'id',
            json_encode(MessageSerializer::pack($message))
        );

        return $this->container->get(MailerInterface::class)->getLast();
    }
}
