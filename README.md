<p align="center">
<img src="https://user-images.githubusercontent.com/2461257/112313394-d926c580-8cb8-11eb-84ea-717df4e4d167.png" width="400" alt="Spiral Framework">
</p>

<p align="center">
<a href="https://packagist.org/packages/spiral/framework"><img src="https://poser.pugx.org/spiral/framework/version"></a>
<a href="https://github.com/spiral/framework/actions"><img src="https://github.com/spiral/framework/workflows/build/badge.svg"></a>
<a href="https://codecov.io/gh/spiral/framework"><img src="https://codecov.io/gh/spiral/framework/graph/badge.svg"></a>
<a href="https://scrutinizer-ci.com/g/spiral/framework/?branch=master"><img src="https://scrutinizer-ci.com/g/spiral/framework/badges/quality-score.png"></a>
</p>

<hr />

<p align="center">
<a href="https://spiral.dev/docs">Documentation</a>
&middot;
<a href="https://discord.gg/TFeEmCs">Discord</a>
&middot;
<a href="https://t.me/spiralphp">Telegram</a>
&middot;
<a href="https://twitter.com/spiralphp">Twitter</a>
</p>

Spiral Framework is a High-Performance Long-Living Full-Stack framework and group of over sixty 
PSR-compatible components. The Framework execution model based on a hybrid runtime where some services 
(GRPC, Queue, WebSockets, etc.) handled by [RoadRunner](https://github.com/spiral/roadrunner) application server and 
the PHP code of your application stays in memory permanently (anti-memory leak tools included).

- [Simple Application](https://github.com/spiral/app)
- [CLI Application](https://github.com/spiral/app-cli)
- [GRPC Application](https://github.com/spiral/app-grpc)
- [Admin Panel](https://github.com/spiral/app-keeper)

## Features

- Battle-tested since 2013
- [Lightning fast full-stack PHP framework](https://www.techempower.com/benchmarks/#section=data-r0&hw=ph&test=fortune&l=yw2xvj-73&c=6&d=1g&a=2&o=e)
- PSR-{3,4,7,11,12,14,15,16,17} compliant
- Powerful [application server](https://roadrunner.dev/) and resident memory application kernel
- Native support of queue (AMQP, SQS, Beanstalk, Kafka) and background PHP workers
- GRPC server and client via [RoadRunner bridge](https://github.com/spiral/roadrunner-bridge)
- Pub/Sub, event broadcasting
- HTTPS, HTTP/2+Push, FastCGI
- PCI DSS compliant
- Encrypted cookies, signed sessions, CSRF-guard
- MySQL, MariaDB, SQLite, PostgreSQL, SQLServer support, auto-migrations
- The [ORM](https://github.com/cycle/orm) via [Cycle Bridge](https://github.com/spiral/cycle-bridge) you will use for the next 25 years
- The [Temporalio](https://github.com/spiral/temporal-bridge) is the simple, scalable open source way to write and run reliable workflows
- Intuitive scaffolding and prototyping (it literally writes code for you)
- Helpful class discovery via static analysis
- Authentication, RBAC security, validation, and encryption
- Dynamic template engine to create your own HTML tags (or just use Twig)
- MVC, HMVC, CQRS, Queue-oriented, RPC-oriented, CLI apps... any apps

## Skeletons
| App Type          | Current Status                                                                                                                 | Install                              |
|-------------------|--------------------------------------------------------------------------------------------------------------------------------|--------------------------------------|
| spiral/app        | [![Latest Stable Version](https://poser.pugx.org/spiral/app/version)](https://packagist.org/packages/spiral/app)               | https://github.com/spiral/app        |
| spiral/app-cli    | [![Latest Stable Version](https://poser.pugx.org/spiral/app-cli/version)](https://packagist.org/packages/spiral/app-cli)       | https://github.com/spiral/app-cli    |
| spiral/app-grpc   | [![Latest Stable Version](https://poser.pugx.org/spiral/app-grpc/version)](https://packagist.org/packages/spiral/app-grpc)     | https://github.com/spiral/app-grpc   |
| spiral/app-keeper | [![Latest Stable Version](https://poser.pugx.org/spiral/app-keeper/version)](https://packagist.org/packages/spiral/app-keeper) | https://github.com/spiral/app-keeper |


## Bridges
| App Type                 | Current Status                                                                                                                               |
|--------------------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| spiral/roadrunner-bridge | [![Latest Stable Version](https://poser.pugx.org/spiral/roadrunner-bridge/version)](https://packagist.org/packages/spiral/roadrunner-bridge) |
| spiral/cycle-bridge      | [![Latest Stable Version](https://poser.pugx.org/spiral/cycle-bridge/version)](https://packagist.org/packages/spiral/cycle-bridge)           |
| spiral/temporal-bridge   | [![Latest Stable Version](https://poser.pugx.org/spiral/temporal-bridge/version)](https://packagist.org/packages/spiral/temporal-bridge)     |
| spiral/data-grid-bridge  | [![Latest Stable Version](https://poser.pugx.org/spiral/data-grid-bridge/version)](https://packagist.org/packages/spiral/data-grid-bridge)   |
| spiral/sapi-bridge       | [![Latest Stable Version](https://poser.pugx.org/spiral/sapi-bridge/version)](https://packagist.org/packages/spiral/sapi-bridge)             |
| spiral/nyholm-bridge     | [![Latest Stable Version](https://poser.pugx.org/spiral/nyholm-bridge/version)](https://packagist.org/packages/spiral/nyholm-bridge)         |

> **Note**
> You can find more community packages in [spiral-packages](https://github.com/spiral-packages/) organization.

License:
--------
MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).
