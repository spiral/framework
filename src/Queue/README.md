# RoadRunner: Background PHP workers, Queue brokers

[![PHP Version Require](https://poser.pugx.org/spiral/queue/require/php)](https://packagist.org/packages/spiral/queue)
[![Latest Stable Version](https://poser.pugx.org/spiral/queue/v/stable)](https://packagist.org/packages/spiral/queue)
[![phpunit](https://github.com/spiral/queue/actions/workflows/phpunit.yml/badge.svg)](https://github.com/spiral/queue/actions)
[![psalm](https://github.com/spiral/queue/actions/workflows/psalm.yml/badge.svg)](https://github.com/spiral/queue/actions)
[![Codecov](https://codecov.io/gh/spiral/queue/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/queue/)
[![Total Downloads](https://poser.pugx.org/spiral/queue/downloads)](https://packagist.org/packages/spiral/queue)
[![type-coverage](https://shepherd.dev/github/spiral/queue/coverage.svg)](https://shepherd.dev/github/spiral/queue)
[![psalm-level](https://shepherd.dev/github/spiral/queue/level.svg)](https://shepherd.dev/github/spiral/queue)
<a href="https://discord.gg/8bZsjYhVVk"><img src="https://img.shields.io/badge/discord-chat-magenta.svg"></a>

<b>[Documentation](https://spiral.dev/docs/queue-configuration)</b> | [Framework Bundle](https://github.com/spiral/framework)

## Features
- supports in memory queue, Beanstalk, AMQP, AWS SQS
- can work as standalone application or as part of RoadRunner server
- multiple pipelines per application
- durable (prefetch control, graceful exit, reconnects)
- automatic queue configuration
- plug-and-play PHP library (framework agnostic)
- delayed jobs
- job level timeouts, retries, retry delays
- PHP and Golang consumers and producers
- per pipeline stop/resume
- interactive stats, events, RPC
- works on Windows

## License:

MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).
