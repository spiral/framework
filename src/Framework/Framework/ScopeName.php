<?php

declare(strict_types=1);

namespace Spiral\Framework;

enum ScopeName: string
{
    case HttpRequest = 'http.request';
    case QueueTask = 'queue.task';
    case TemporalActivity = 'temporal.activity';
    case GrpcRequest = 'grpc.request';
    case CentrifugoRequest = 'centrifugo.request';
    case TcpPacket = 'tcp.packet';
    case ConsoleCommand = 'console.command';
}
