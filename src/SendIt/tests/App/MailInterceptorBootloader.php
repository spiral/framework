<?php

declare(strict_types=1);

namespace Spiral\Tests\SendIt\App;

use Spiral\Boot\Bootloader\Bootloader;
use Symfony\Component\Mailer\MailerInterface;

final class MailInterceptorBootloader extends Bootloader
{
    protected const SINGLETONS = [
        MailerInterface::class => MailInterceptor::class,
    ];
}
