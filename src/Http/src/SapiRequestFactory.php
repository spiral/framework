<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * @source https://github.com/yiisoft/yii-web/blob/master/src/ServerRequestFactory.php
 * Used by permission from Alex Makarov.
 *
 * Copyright © 2008 by Yii Software LLC (http://www.yiisoft.com) All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list
 * of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright notice, this list
 * of conditions and the following disclaimer in the documentation and/or other materials provided
 * with the distribution.
 *
 * Neither the name of Yii Software LLC nor the names of its contributors may be used to
 * endorse or promote products derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 *
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @codeCoverageIgnore
 */
final class SapiRequestFactory
{
    public function __construct(
        private readonly ServerRequestFactoryInterface $requestFactory,
        private readonly UriFactoryInterface $uriFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly UploadedFileFactoryInterface $uploadedFileFactory
    ) {
    }

    public function fromGlobals(): ServerRequestInterface
    {
        return $this->createFromParameters(
            $_SERVER,
            self::getHeadersFromGlobals(),
            $_COOKIE,
            $_GET,
            $_POST,
            $_FILES,
            \fopen('php://input', 'rb') ?: null
        );
    }

    public function createFromParameters(
        array $server,
        array $headers = [],
        array $cookies = [],
        array $get = [],
        array $post = [],
        array $files = [],
        mixed $body = null
    ): ServerRequestInterface {
        $method = $server['REQUEST_METHOD'] ?? 'GET';

        $uri = $this->getUri($server);

        $request = $this->requestFactory->createServerRequest($method, $uri, $server);
        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        $protocol = '1.1';
        if (!empty($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = \str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']);
        }

        $request = $request
            ->withProtocolVersion($protocol)
            ->withQueryParams($get)
            ->withParsedBody($post)
            ->withCookieParams($cookies)
            ->withUploadedFiles($this->getUploadedFilesArray($files));

        if ($body === null) {
            return $request;
        }

        if (\is_resource($body)) {
            $body = $this->streamFactory->createStreamFromResource($body);
        } elseif (\is_string($body)) {
            $body = $this->streamFactory->createStream($body);
        } elseif (!$body instanceof StreamInterface) {
            throw new \InvalidArgumentException(
                'Body parameter for ServerRequestFactory::createFromParameters() '
                . 'must be instance of StreamInterface, resource or null.'
            );
        }

        return $request->withBody($body);
    }

    private function getUri(array $server): UriInterface
    {
        $uri = $this->uriFactory->createUri();
        if (isset($server['HTTPS'])) {
            $uri = $uri->withScheme($server['HTTPS'] === 'on' ? 'https' : 'http');
        }

        if (isset($server['HTTP_HOST'])) {
            if (1 === \preg_match('/^(.+)\:(\d+)$/', $server['HTTP_HOST'], $matches)) {
                $uri = $uri->withHost($matches[1])->withPort($matches[2]);
            } else {
                $uri = $uri->withHost($server['HTTP_HOST']);
            }
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        }

        if (isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort($server['SERVER_PORT']);
        }

        if (isset($server['REQUEST_URI'])) {
            $uri = $uri->withPath(\explode('?', (string) $server['REQUEST_URI'])[0]);
        }

        if (isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }

    private static function getHeadersFromGlobals(): array
    {
        if (\function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (\str_starts_with($name, 'HTTP_')) {
                    $name = \str_replace(' ', '-', \ucwords(\strtolower(\str_replace('_', ' ', \substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }

        return $headers;
    }

    private function getUploadedFilesArray(array $filesArray): array
    {
        $files = [];
        foreach ($filesArray as $class => $info) {
            $files[$class] = [];
            $this->populateUploadedFileRecursive(
                $files[$class],
                $info['name'],
                $info['tmp_name'],
                $info['type'],
                $info['size'],
                $info['error']
            );
        }

        return $files;
    }

    /**
     * Populates uploaded files array from $_FILE data structure recursively.
     *
     * @param array $files            uploaded files array to be populated.
     * @param array|string $names     file names provided by PHP
     * @param array|string $tempNames temporary file names provided by PHP
     * @param array|string $types     file types provided by PHP
     * @param array|int $sizes        file sizes provided by PHP
     * @param array|int $errors       uploading issues provided by PHP
     * @since 3.0.0
     */
    private function populateUploadedFileRecursive(
        array &$files,
        array|string $names,
        array|string $tempNames,
        array|string $types,
        array|int $sizes,
        array|int $errors
    ): void {
        if (\is_array($names)) {
            foreach ($names as $i => $name) {
                $files[$i] = [];
                $this->populateUploadedFileRecursive(
                    $files[$i],
                    $name,
                    $tempNames[$i],
                    $types[$i],
                    $sizes[$i],
                    $errors[$i]
                );
            }
        } else {
            try {
                $stream = $this->streamFactory->createStreamFromFile($tempNames);
            } catch (\RuntimeException) {
                $stream = $this->streamFactory->createStream();
            }

            $files = $this->uploadedFileFactory->createUploadedFile(
                $stream,
                (int)$sizes,
                (int)$errors,
                $names,
                $types
            );
        }
    }
}
