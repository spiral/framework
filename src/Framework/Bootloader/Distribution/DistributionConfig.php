<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Distribution;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Uri as GuzzleUri;
use Laminas\Diactoros\Uri as LaminasUri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Distribution\Manager;
use Spiral\Distribution\Resolver\CloudFrontResolver;
use Spiral\Distribution\Resolver\S3SignedResolver;
use Spiral\Distribution\Resolver\StaticResolver;
use Spiral\Distribution\ResolverInterface;
use Spiral\Config\Exception\InvalidArgumentException;

class DistributionConfig
{
    /**
     * @var string
     */
    public const CONFIG = 'distribution';

    /**
     * @var string
     */
    private $default = Manager::DEFAULT_RESOLVER;

    /**
     * @var array<string, ResolverInterface>
     */
    private $resolvers = [];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->bootDefaultDriver($config);
        $this->bootResolvers($config);
    }

    /**
     * @param array $config
     */
    private function bootResolvers(array $config): void
    {
        foreach ($config['resolvers'] ?? [] as $name => $child) {
            if (! \is_string($name)) {
                throw new InvalidArgumentException(
                    \vsprintf('Distribution driver config key must be a string, but %s given', [
                        \get_debug_type($child)
                    ])
                );
            }

            if (! \is_array($child)) {
                throw new InvalidArgumentException(
                    \vsprintf('Distribution driver config `%s` must be an array, but %s given', [
                        $name,
                        \get_debug_type($child)
                    ])
                );
            }

            $this->resolvers[$name] = $this->createResolver($name, $child);
        }
    }

    /**
     * @param array $config
     */
    private function bootDefaultDriver(array $config): void
    {
        $default = $config['default'] ?? null;

        if ($default !== null) {
            // Validate config
            if (! \is_string($default)) {
                throw new InvalidArgumentException(
                    \vsprintf('Distribution config default driver must be a string, but %s given', [
                        \get_debug_type($default)
                    ])
                );
            }

            $this->default = $default;
        }
    }

    /**
     * @param string $name
     * @param array $config
     * @return ResolverInterface
     */
    private function createResolver(string $name, array $config): ResolverInterface
    {
        if (!isset($config['type']) || !\is_string($config['type'])) {
            throw $this->invalidConfigKey($name, 'type', 'string');
        }

        $type = $config['type'];

        switch ($type) {
            case 'static':
                return $this->createStaticResolver($name, $config);

            case 's3':
                return $this->createS3Resolver($name, $config);

            case 'cloudfront':
                return $this->createCloudFrontResolver($name, $config);

            default:
                return $this->createCustomResolver($type, $name, $config);
        }
    }

    /**
     * @param string $type
     * @param string $name
     * @param array $config
     * @return ResolverInterface
     */
    private function createCustomResolver(string $type, string $name, array $config): ResolverInterface
    {
        if (! \is_subclass_of($type, ResolverInterface::class, true)) {
            throw $this->invalidConfigKey($name, 'type', ResolverInterface::class);
        }

        if (isset($config['options']) && !\is_array($config['options'])) {
            throw $this->invalidConfigKey($name, 'options', 'array');
        }

        try {
            return new $type(...\array_values($config['options'] ?? []));
        } catch (\Throwable $e) {
            $message = 'An error occurred while resolver `%s` initializing: %s';
            throw new InvalidArgumentException(\sprintf($message, $name, $e->getMessage()), 0, $e);
        }
    }

    /**
     * @param string $name
     * @param array $config
     * @return ResolverInterface
     */
    private function createS3Resolver(string $name, array $config): ResolverInterface
    {
        // Required config options
        if (!isset($config['region']) || !\is_string($config['region'])) {
            throw $this->invalidConfigKey($name, 'region', 'string');
        }

        if (!isset($config['key']) || !\is_string($config['key'])) {
            throw $this->invalidConfigKey($name, 'key', 'string');
        }

        if (!isset($config['secret']) || !\is_string($config['secret'])) {
            throw $this->invalidConfigKey($name, 'secret', 'string');
        }

        if (!isset($config['bucket']) || !\is_string($config['bucket'])) {
            throw $this->invalidConfigKey($name, 'bucket', 'string');
        }

        // Optional config options
        if (!\is_string($config['version'] ?? '')) {
            throw $this->invalidConfigKey($name, 'version', 'string or null');
        }

        if (!\is_string($config['token'] ?? '')) {
            throw $this->invalidConfigKey($name, 'token', 'string or null');
        }

        if (!\is_int($config['expires'] ?? 0)) {
            throw $this->invalidConfigKey($name, 'expires', 'positive int (unix timestamp)');
        }

        $client = new S3Client([
            'version'     => $config['version'] ?? 'latest',
            'region'      => $config['region'],
            'credentials' => new Credentials(
                $config['key'],
                $config['secret'],
                $config['token'] ?? null,
                $config['expires'] ?? null
            ),
        ]);

        return new S3SignedResolver($client, $config['bucket']);
    }

    /**
     * @param string $name
     * @param array $config
     * @return ResolverInterface
     */
    private function createCloudFrontResolver(string $name, array $config): ResolverInterface
    {
        if (!isset($config['key']) || !\is_string($config['key'])) {
            throw $this->invalidConfigKey($name, 'key', 'string');
        }

        if (!isset($config['private']) || !\is_string($config['private'])) {
            throw $this->invalidConfigKey($name, 'private', 'string value or path to key file');
        }

        if (!isset($config['domain']) || !\is_string($config['domain'])) {
            throw $this->invalidConfigKey($name, 'domain', 'string');
        }

        return new CloudFrontResolver(
            $config['key'],
            $config['private'],
            $config['domain']
        );
    }

    /**
     * @param string $name
     * @param array $config
     * @return ResolverInterface
     */
    private function createStaticResolver(string $name, array $config): ResolverInterface
    {
        if (!isset($config['uri']) || !\is_string($config['uri'])) {
            throw $this->invalidConfigKey($name, 'uri', 'string');
        }

        return new StaticResolver($this->createUri($name, $config['uri'], $config));
    }

    /**
     * @param string $name
     * @param string $uri
     * @param array $config
     * @return UriInterface
     */
    private function createUri(string $name, string $uri, array $config): UriInterface
    {
        if (!\is_string($config['factory'] ?? '')) {
            throw $this->invalidConfigKey($name, 'factory', 'string (PSR-7 uri factory implementation)');
        }

        switch (true) {
            case isset($config['factory']):
                /** @var UriFactoryInterface $factory */
                $factory = new $config['factory'];

                if (! $factory instanceof UriFactoryInterface) {
                    $message = 'Distribution config driver `%s` should contain class that must be a valid PSR-7 ' .
                        'uri factory implementation, but `%s` given';
                    throw new InvalidArgumentException(\sprintf($message, $name, $config['factory']));
                }

                return $factory->createUri($uri);

            case \class_exists(LaminasUri::class):
                return new LaminasUri($uri);

            case \class_exists(GuzzleUri::class):
                return new GuzzleUri($uri);

            default:
                $message = 'Can not resolve available PSR-7 UriFactory implementation; ' .
                    'Please define `factory` config section in `%s` distribution driver config';
                throw new InvalidArgumentException(\sprintf($message, $name));
        }
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $type
     * @return InvalidArgumentException
     */
    private function invalidConfigKey(string $name, string $key, string $type): InvalidArgumentException
    {
        $message = 'Distribution config of `%s` driver must contain key `%s` that must be a type of %s';

        return new InvalidArgumentException(\sprintf($message, $name, $key, $type));
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->default;
    }

    /**
     * @return iterable<string, ResolverInterface>
     */
    public function getResolvers(): iterable
    {
        return $this->resolvers;
    }
}
