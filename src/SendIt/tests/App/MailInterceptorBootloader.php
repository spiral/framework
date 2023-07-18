<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt\App;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Symfony\Component\Mailer\MailerInterface;

class MailInterceptorBootloader extends Bootloader
{
    public function init(Container $container): void
    {
        $container->removeBinding(MailerInterface::class);
        $container->bindSingleton(MailerInterface::class, MailInterceptor::class);
    }
}
