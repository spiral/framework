<center>
<img src="https://user-images.githubusercontent.com/2461257/112311949-4fc2c380-8cb7-11eb-8e2c-882b62b0b408.png" width="400" alt="Spiral Framework">

[![Latest Stable Version](https://poser.pugx.org/spiral/framework/version)](https://packagist.org/packages/spiral/framework)
[![Build Status](https://github.com/spiral/framework/workflows/build/badge.svg)](https://github.com/spiral/framework/actions)
[![Codecov](https://codecov.io/gh/spiral/framework/graph/badge.svg)](https://codecov.io/gh/spiral/framework)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral/framework/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral/framework/?branch=master) 

----

[Documentation](https://spiral.dev/docs) &middot;
[Discord](https://discord.gg/TFeEmCs) &middot; 
[Telegram](https://t.me/spiralphp) &middot; 
[Twitter](https://twitter.com/spiralphp)

</center>

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
- [Lightning fast full-stack PHP framework](https://www.techempower.com/benchmarks/#section=test&runid=ba6c12a4-d54d-4a9e-825b-abde0ef6d6a2&hw=ph&test=fortune&l=zg24n3-f&c=6&a=2&o=e)
- PSR-{2,3,4,6,7,11,15,16,17} compliant
- Powerful [application server](https://roadrunner.dev/) and resident memory application kernel
- Native support of queue (AMQP, SQS, Beanstalk) and background PHP workers
- GRPC server and client
- Pub/Sub, event broadcasting
- HTTPS, HTTP/2+Push, FastCGI
- PCI DSS compliant
- Encrypted cookies, signed sessions, CSRF-guard
- MySQL, MariaDB, SQLite, PostgreSQL, SQLServer support, auto-migrations
- The [ORM](https://github.com/cycle/orm) you will use for the next 25 years
- Intuitive scaffolding and prototyping (it literally writes code for you)
- Helpful class discovery via static analysis
- Authentication, RBAC security, validation, and encryption
- Dynamic template engine to create your own HTML tags (or just use Twig)
- MVC, HMVC, CQRS, Queue-oriented, RPC-oriented, CLI apps... any apps

## Skeletons
| App Type | Current Status | Install       
| ---       | --- | ---
spiral/app | [![Latest Stable Version](https://poser.pugx.org/spiral/app/version)](https://packagist.org/packages/spiral/app) | https://github.com/spiral/app
spiral/app-cli | [![Latest Stable Version](https://poser.pugx.org/spiral/app-cli/version)](https://packagist.org/packages/spiral/app-cli) | https://github.com/spiral/app-cli
spiral/app-grpc | [![Latest Stable Version](https://poser.pugx.org/spiral/app-grpc/version)](https://packagist.org/packages/spiral/app-grpc) | https://github.com/spiral/app-grpc
spiral/app-keeper | [![Latest Stable Version](https://poser.pugx.org/spiral/app-keeper/version)](https://packagist.org/packages/spiral/app-keeper) | https://github.com/spiral/app-keeper

License:
--------
MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).
