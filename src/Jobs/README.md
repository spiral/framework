# RoadRunner: Background PHP workers, Queue brokers
[![Latest Stable Version](https://poser.pugx.org/spiral/jobs/version)](https://packagist.org/packages/spiral/jobs)
[![GoDoc](https://godoc.org/github.com/spiral/jobs?status.svg)](https://godoc.org/github.com/spiral/jobs)
[![CI](https://github.com/spiral/jobs/workflows/CI/badge.svg)](https://github.com/spiral/jobs/actions)
[![Go Report Card](https://goreportcard.com/badge/github.com/spiral/jobs)](https://goreportcard.com/report/github.com/spiral/jobs)
[![Codecov](https://codecov.io/gh/spiral/jobs/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/jobs/)


## Documentation
  * [Installation and Configuration](https://spiral.dev/docs/queue-configuration)
  * [Console Commands](https://spiral.dev/docs/queue-commands)
  * [Running Jobs](https://spiral.dev/docs/queue-jobs)
  * [Standalone Usage](https://spiral.dev/docs/queue-standalone)

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

License:
--------
The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).
