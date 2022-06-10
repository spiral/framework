<?php

declare(strict_types=1);

namespace Spiral\Serializer\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Serializer\Serializer\JsonSerializer;
use Spiral\Serializer\Serializer\PhpSerializer;
use Spiral\Serializer\SerializerCollection;
use Spiral\Serializer\SerializerManager;

final class SerializerBootloader extends Bootloader
{
    protected const SINGLETONS = [
        SerializerManager::class => [self::class, 'initSerializer'],
        SerializerCollection::class => SerializerCollection::class,
    ];

    private function initSerializer(SerializerCollection $serializers): SerializerManager
    {
        // The serializer isn't configured. Adding default serializers
        if (!$serializers->count()) {
            $serializers->add('json', new JsonSerializer());
            $serializers->add('serialize', new PhpSerializer());
        }

        return new SerializerManager($serializers);
    }
}
