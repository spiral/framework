SendIt
========
[![Latest Stable Version](https://poser.pugx.org/spiral/sendit/v/stable)](https://packagist.org/packages/spiral/sendit) 
[![Build Status](https://github.com/spiral/sendit/workflows/build/badge.svg)](https://github.com/spiral/sendit/actions)
[![Codecov](https://codecov.io/gh/spiral/sendit/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/sendit/)

Email builder and queue handler.

Installation:
--------
To install the component:

```bash
$ composer require spiral/sendit
```

> Make sure to install required `symfony/mailer` drivers.

Activate the component bootloaders:

```php
use Spiral\SendIt\Bootloader as Sendit;
// ...

protected const LOAD = [
    // ...
    Sendit\MailerBootloader::class,
    Sendit\BuilderBootloader::class,
    // ...
];
```

Configure the component using `app/config/mailer.php` file:

```php
// Amazon SES
return [
    'dsn'  => sprintf(
        'ses+smtps://%s:%s@ses?region=%s',
        rawurlencode(env('AWS_KEY')),
        rawurlencode(env('AWS_SECRET')),
        env('AWS_REGION')
    ),
    'from' => 'Project Name <no-reply@project.com>',
];
```

Or specify ENV variables:

```dotenv
MAILER_DSN = "smtp://..."
MAILER_FROM = "Project Name <no-reply@project.com>"
```

Example:
--------
The component provides the ability to compose content-rich email templates using Stempler views:

```html
<extends:sendit:builder subject="I'm afraid I can't do that"/>
<use:bundle path="sendit:bundle"/>

<email:attach path="{{ $attachment }}" name="attachment.txt"/>

<block:html>
    <p>I'm sorry, {{ $name }}!</p>
    <p><email:image path="path/to/image.png"/></p>
</block:html>
```

To use:

```php
use Spiral\Mailer;

function send(Mailer\MailerInterface $mailer)
{
    $mailer->send(new Mailer\Message(
        "template.dark.php", 
        "email@domain.com",
        [
            "name" => "Dave",
            "attachment" => __FILE__,
        ]
    ));
}
```

License:
--------
MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).
