# Spiral - High-Performance PHP/Go Framework
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/version)](https://packagist.org/packages/spiral/framework)
[![Build Status](https://github.com/spiral/framework/workflows/build/badge.svg)](https://github.com/spiral/framework/actions)
[![Codecov](https://codecov.io/gh/spiral/framework/graph/badge.svg)](https://codecov.io/gh/spiral/framework)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral/framework/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral/framework/?branch=master) <a href="https://discord.gg/TFeEmCs"><img src="https://img.shields.io/badge/discord-chat-magenta.svg"></a>

<img src="https://user-images.githubusercontent.com/796136/67560465-9d827780-f723-11e9-91ac-9b2fafb027f2.png" height="135px" alt="Spiral Framework" align="left"/>

Spiral Framework is a High-Performance PHP/Go Full-Stack framework and over sixty PSR compatible components. The Framework execution model is based on a hybrid runtime where some services (GRPC, Queue, WebSockets, etc) handled on Application Server - RoadRunner. The PHP code of your application will stay in memory between the requests (the framework provides a set of instruments to avoid memory leaks). 


[App Skeleton](https://github.com/spiral/app) ([CLI](https://github.com/spiral/app-cli), [GRPC](https://github.com/spiral/app-grpc), [Admin Panel](https://github.com/spiral/app-keeper)) | [spiral.dev](https://spiral.dev/) | [**Documentation**](https://spiral.dev/docs) | [Twitter](https://twitter.com/spiralphp) | [CHANGELOG](/CHANGELOG.md) | [Contributing](https://github.com/spiral/guide/blob/master/contributing.md)

<br/>

## Features
- Battle-tested since 2013
- [Lightning fast full-stack PHP framework](https://www.techempower.com/benchmarks/#section=test&runid=92383925-3ba7-40fd-88cf-19f55751f01c&hw=ph&test=fortune&l=zik073-v&c=6)
- PSR-{2,3,4,6,7,11,15,16,17} compliant
- Powerful [application server](https://roadrunner.dev/) and resident memory application kernel
- Native support of queue (AMQP, SQS, Beanstalk) and background PHP workers
- GRPC server and client
- Pub/Sub, event broadcasting
- HTTPS, HTTP/2+Push, FastCGI
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
