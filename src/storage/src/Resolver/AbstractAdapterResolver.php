<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Resolver;

use Spiral\Storage\Config\ConfigInterface;
use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Exception\StorageException;
use Spiral\Storage\Exception\UriException;
use Spiral\Storage\Parser\UriParserInterface;

/**
 * Abstract class for any resolver
 * Depends on adapter by default
 */
abstract class AbstractAdapterResolver implements AdapterResolverInterface
{
    /**
     * Filesystem info class required for resolver
     * In case other filesystem info will be provided - exception will be thrown
     */
    protected const FILE_SYSTEM_INFO_CLASS = '';

    /**
     * @var FileSystemInfoInterface
     */
    protected $fsInfo;

    /**
     * @var UriParserInterface
     */
    protected $uriParser;

    /**
     * @param UriParserInterface $uriParser
     * @param ConfigInterface $config
     * @param string $fs
     *
     * @throws StorageException
     */
    public function __construct(UriParserInterface $uriParser, ConfigInterface $config, string $fs)
    {
        $requiredClass = static::FILE_SYSTEM_INFO_CLASS;

        $fsInfo = $config->buildFileSystemInfo($fs);

        if (empty($requiredClass) || !$fsInfo instanceof $requiredClass) {
            throw new StorageException(
                \sprintf(
                    'Wrong filesystem info (`%s`) for resolver `%s`',
                    get_class($fsInfo),
                    static::class
                )
            );
        }

        $this->uriParser = $uriParser;

        $this->fsInfo = $fsInfo;
    }

    /**
     * Normalize filepath for filesystem operation
     * In case uri provided path to file will be extracted
     * In case filepath provided it will be returned
     *
     * @param string $filePath
     *
     * @return string
     */
    public function normalizeFilePath(string $filePath): string
    {
        try {
            $uri = $this->uriParser->parse($filePath);

            return $uri->getPath();
        } catch (UriException $e) {
            // if filePath is not uri we suppose it is short form of filepath - without fs part
        }

        return $filePath;
    }
}
