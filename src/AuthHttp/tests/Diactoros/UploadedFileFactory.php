<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Diactoros;

use Nyholm\Psr7\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

final class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = \UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
    ): UploadedFileInterface {
        $size ??= $stream->getSize();

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}
