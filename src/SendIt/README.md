# Spiral Framework: SendIt

[![PHP Version Require](https://poser.pugx.org/spiral/sendit/require/php)](https://packagist.org/packages/spiral/sendit)
[![Latest Stable Version](https://poser.pugx.org/spiral/sendit/v/stable)](https://packagist.org/packages/spiral/sendit)
[![phpunit](https://github.com/spiral/sendit/actions/workflows/phpunit.yml/badge.svg)](https://github.com/spiral/sendit/actions)
[![psalm](https://github.com/spiral/sendit/actions/workflows/psalm.yml/badge.svg)](https://github.com/spiral/sendit/actions)
[![Codecov](https://codecov.io/gh/spiral/sendit/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/sendit/)
[![Total Downloads](https://poser.pugx.org/spiral/sendit/downloads)](https://packagist.org/packages/spiral/sendit)
[![type-coverage](https://shepherd.dev/github/spiral/sendit/coverage.svg)](https://shepherd.dev/github/spiral/sendit)
[![psalm-level](https://shepherd.dev/github/spiral/sendit/level.svg)](https://shepherd.dev/github/spiral/sendit)
<a href="https://discord.gg/8bZsjYhVVk"><img src="https://img.shields.io/badge/discord-chat-magenta.svg"></a>

<b>[Documentation](https://spiral.dev/docs/component-sendit)</b> | [Framework Bundle](https://github.com/spiral/framework)

## License:

MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).


## Config example

```php
// config/mailer.php

return [
    /**
     * The default mailer that is used to send any email messages sent by your application.
     * @see https://symfony.com/doc/current/mailer.html#using-built-in-transports
     */
    'dsn' => env('MAILER_DSN', 'smtp://user:pass@mailhog:25'),

    /**
     * Global "From" Address
     */
    'from' => env('MAILER_FROM', 'Spiral <sendit@local.host>'),

    /**
     * A queue connection in that any email messages will be pushed.
     */
    'queueConnection' => env('MAILER_QUEUE_CONNECTION', 'sync'),
    'queue' => env('MAILER_QUEUE', 'local'),
    
    /**
     * List of class-string implementations of \Symfony\Component\Mailer\Transport\TransportFactoryInterface.
     * If empty, values will be taken from \Symfony\Component\Mailer\Transport::getDefaultFactories()
     */
    'transportFactories' => [
        // buildin
        NullTransportFactory::class,
        SendmailTransportFactory::class,
        EsmtpTransportFactory::class,
        NativeTransportFactory::class,
        // extension
        GmailTransportFactory::class,
        InfobipTransportFactory::class,
        MailgunTransportFactory::class,
        MailjetTransportFactory::class,
        MandrillTransportFactory::class,
        OhMySmtpTransportFactory::class,
        PostmarkTransportFactory::class,
        SendgridTransportFactory::class,
        SendinblueTransportFactory::class,
        SesTransportFactory::class,
        // custom
        MyTransportFactory::class
    ]
];
```
