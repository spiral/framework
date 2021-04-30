<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Storage\Config\ConfigInterface;
use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Config\StorageConfig;
use Spiral\Storage\Exception\ResolveException;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Parser\UriParser;
use Spiral\Storage\Parser\UriParserInterface;
use Spiral\Storage\Resolver\AdapterResolverInterface;

/**
 * @psalm-import-type UriLikeType from UriResolverInterface
 */
class UriResolver implements UriResolverInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var UriParserInterface
     */
    protected $parser;

    /**
     * @var array<AdapterResolverInterface>
     */
    protected $resolvers = [];

    /**
     * @param ConfigInterface $config
     * @param UriParserInterface|null $uriParser
     */
    public function __construct(ConfigInterface $config, UriParserInterface $parser = null)
    {
        $this->config = $config;
        $this->parser = $parser ?? new UriParser();
    }

    /**
     * {@inheritDoc}
     */
    public function resolveAll(iterable $uris): iterable
    {
        foreach ($uris as $uri) {
            yield $this->resolve($uri);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($uri)
    {
        try {
            $uri = $this->parser->parse($uri);

            return $this->getResolver($uri->getFileSystem())
                ->buildUrl($uri->getPath())
            ;
        } catch (StorageException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new ResolveException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Get resolver for filesystem by key
     *
     * @param string $fileSystem
     *
     * @return AdapterResolverInterface
     *
     * @throws StorageException
     */
    protected function getResolver(string $fileSystem): AdapterResolverInterface
    {
        if (!\array_key_exists($fileSystem, $this->resolvers)) {
            $this->resolvers[$fileSystem] = $this->prepareResolverForFileSystem(
                $this->config->buildFileSystemInfo($fileSystem)
            );
        }

        return $this->resolvers[$fileSystem];
    }

    /**
     * Prepare resolver by provided filesystem info
     *
     * @param FileSystemInfoInterface $fsInfo
     *
     * @return AdapterResolverInterface
     */
    protected function prepareResolverForFileSystem(FileSystemInfoInterface $fsInfo): AdapterResolverInterface
    {
        $resolver = $fsInfo->getResolverClass();

        return new $resolver($this->parser, $this->config, $fsInfo->getName());
    }
}
