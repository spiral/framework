// MIT License
//
// Copyright (c) 2019 SpiralScout
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

package main

import (
	rr "github.com/spiral/roadrunner/cmd/rr/cmd"

	// services (plugins)
	"github.com/spiral/jobs"
	"github.com/spiral/php-grpc"
	"github.com/spiral/roadrunner/service/env"
	"github.com/spiral/roadrunner/service/headers"
	"github.com/spiral/roadrunner/service/http"
	"github.com/spiral/roadrunner/service/rpc"
	"github.com/spiral/roadrunner/service/static"
    "github.com/spiral/roadrunner/service/limit"
    "github.com/spiral/roadrunner/service/metrics"

	// queue brokers
	"github.com/spiral/jobs/broker/amqp"
	"github.com/spiral/jobs/broker/beanstalk"
	"github.com/spiral/jobs/broker/ephemeral"
	"github.com/spiral/jobs/broker/sqs"

	// additional commands and debug handlers
	_ "github.com/spiral/jobs/cmd/rr-jobs/jobs"
	_ "github.com/spiral/php-grpc/cmd/rr-grpc/grpc"
	_ "github.com/spiral/roadrunner/cmd/rr/http"
	_ "github.com/spiral/roadrunner/cmd/rr/limit"
)

func main() {
	rr.Container.Register(env.ID, &env.Service{})
	rr.Container.Register(rpc.ID, &rpc.Service{})

    // http
	rr.Container.Register(http.ID, &http.Service{})
	rr.Container.Register(headers.ID, &headers.Service{})
    rr.Container.Register(static.ID, &static.Service{})

	rr.Container.Register(grpc.ID, &grpc.Service{})

	rr.Container.Register(jobs.ID, &jobs.Service{
		Brokers: map[string]jobs.Broker{
			"amqp":      &amqp.Broker{},
			"ephemeral": &ephemeral.Broker{},
			"beanstalk": &beanstalk.Broker{},
			"sqs":       &sqs.Broker{},
		},
	})

	// supervisor and metrics
	rr.Container.Register(limit.ID, &limit.Service{})
    rr.Container.Register(metrics.ID, &metrics.Service{})

	// you can register additional commands using cmd.CLI
	rr.Execute()
}
