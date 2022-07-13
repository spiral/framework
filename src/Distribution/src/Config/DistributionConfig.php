<?php

declare(strict_types=1);

namespace Spiral\Distribution\Config;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Uri as GuzzleUri;
use Nyholm\Psr7\Uri as NyholmUri;
use Psr\Http\Message\UriInterface;
use Spiral\Core\InjectableConfig;
use Spiral\Distribution\Manager;
use Spiral\Distribution\Resolver\CloudFrontResolver;
use Spiral\Distribution\Resolver\S3SignedResolver;
use Spiral\Distribution\Resolver\StaticResolver;
use Spiral\Distribution\UriResolverInterface;
use Spiral\Config\Exception\InvalidArgumentException;

final class DistributionConfig extends InjectableConfig
{
    public const CONFIG = 'distribution';

    private string $default = Manager::DEFAULT_RESOLVER;

    /**
     * @var array<string, UriResolverInterface>
     */
    private array $resolvers = [];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->bootDefaultDriver($config);
        $this->bootResolvers($config);
    }

    public function getDefaultDriver(): string
    {
        return $this->default;
    }

    /**
     * @return iterable<string, UriResolverInterface>
     */
    public function getResolvers(): iterable
    {
        return $this->resolvers;
    }

    private function bootResolvers(array $config): void
    {
        foreach ($config['resolvers'] ?? [] as $name => $child) {
            if (!\is_string($name)) {
                throw new InvalidArgumentException(
                    \vsprintf('Distribution driver config key must be a string, but %s given', [
                        \get_debug_type($child),
                    ])
                );
            }

            if (!\is_array($child)) {
                throw new InvalidArgumentException(
                    \vsprintf('Distribution driver config `%s` must be an array, but %s given', [
                        $name,
                        \get_debug_type($child),
                    ])
                );
            }

            $this->resolvers[$name] = $this->createResolver($name, $child);
        }
    }

    private function bootDefaultDriver(array $config): void
    {
        $default = $config['default'] ?? null;

        if ($default !== null) {
            // Validate config
            if (!\is_string($default)) {
                throw new InvalidArgumentException(
                    \vsprintf('Distribution config default driver must be a string, but %s given', [
                        \get_debug_type($default),
                    ])
                );
            }

            $this->default = $default;
        }
    }

    private function createResolver(string $name, array $config): UriResolverInterface
    {
        if (!isset($config['type']) || !\is_string($config['type'])) {
            throw $this->invalidConfigKey($name, 'type', 'string');
        }

        return match ($config['type']) {
            'static' => $this->createStaticResolver($name, $config),
            's3' => $this->createS3Resolver($name, $config),
            'cloudfront' => $this->createCloudFrontResolver($name, $config),
            default => $this->createCustomResolver($config['type'], $name, $config),
        };
    }

    private function createCustomResolver(string $type, string $name, array $config): UriResolverInterface
    {
        if (!\is_subclass_of($type, UriResolverInterface::class, true)) {
            throw $this->invalidConfigKey($name, 'type', UriResolverInterface::class);
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

    private function createS3Resolver(string $name, array $config): UriResolverInterface
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
        if (!\is_string($config['prefix'] ?? '')) {
            throw $this->invalidConfigKey($name, 'prefix', 'string or null');
        }

        if (!\is_string($config['version'] ?? '')) {
            throw $this->invalidConfigKey($name, 'version', 'string or null');
        }

        if (!\is_string($config['token'] ?? '')) {
            throw $this->invalidConfigKey($name, 'token', 'string or null');
        }

        if (!\is_int($config['expires'] ?? 0)) {
            throw $this->invalidConfigKey($name, 'expires', 'positive int (unix timestamp)');
        }

        $s3Options = [
            'version'     => $config['version'] ?? 'latest',
            'region'      => $config['region'],
            'endpoint'    => $config['endpoint'] ?? null,
            'credentials' => new Credentials(
                $config['key'],
                $config['secret'],
                $config['token'] ?? null,
                $config['expires'] ?? null
            ),
        ];

        $s3Options += ($config['options'] ?? []);

        $client = new S3Client($s3Options);

        return new S3SignedResolver($client, $config['bucket'], $config['prefix'] ?? null);
    }

    private function createCloudFrontResolver(string $name, array $config): UriResolverInterface
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

        if (!\is_string($config['prefix'] ?? '')) {
            throw $this->invalidConfigKey($name, 'prefix', 'string or null');
        }

        return new CloudFrontResolver(
            $config['key'],
            $config['private'],
            $config['domain'],
            $config['prefix'] ?? null
        );
    }

    private function createStaticResolver(string $name, array $config): UriResolverInterface
    {
        if (!isset($config['uri']) || !\is_string($config['uri'])) {
            throw $this->invalidConfigKey($name, 'uri', 'string');
        }

        return new StaticResolver($this->createUri($name, $config['uri'], $config));
    }

    private function createUri(string $name, string $uri, array $config): UriInterface
    {
        if (!\is_string($config['factory'] ?? '')) {
            throw $this->invalidConfigKey($name, 'factory', 'string (PSR-7 uri factory implementation)');
        }

        return match (true) {
            isset($config['factory']) => (new $config['factory']())->createUri($uri),
            \class_exists(NyholmUri::class) => new NyholmUri($uri),
            \class_exists(GuzzleUri::class) => new GuzzleUri($uri),
            default => throw new InvalidArgumentException(
                \sprintf('Can not resolve available PSR-7 UriFactory implementation; 
                    Please define `factory` config section in `%s` distribution driver config', $name)
            )
        };
    }

    private function invalidConfigKey(string $name, string $key, string $type): InvalidArgumentException
    {
        $message = 'Distribution config of `%s` driver must contain key `%s` that must be a type of %s';

        return new InvalidArgumentException(\sprintf($message, $name, $key, $type));
    }
}
