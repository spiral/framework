<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Resolver;

use Spiral\Storage\Config\DTO\FileSystemInfo\LocalInfo;
use Spiral\Storage\Config\DTO\FileSystemInfo\FileSystemInfoInterface;
use Spiral\Storage\Exception\ResolveException;

class LocalSystemResolver extends AbstractAdapterResolver
{
    protected const FILE_SYSTEM_INFO_CLASS = LocalInfo::class;

    /**
     * @var FileSystemInfoInterface|LocalInfo
     */
    protected $fsInfo;

    /**
     * @param string $uri
     * @param array $options
     *
     * @return string
     *
     * @throws ResolveException
     */
    public function buildUrl(string $uri, array $options = [])
    {
        if (!$this->fsInfo->hasOption(LocalInfo::HOST_KEY)) {
            throw new ResolveException(
                \sprintf('Url can\'t be built for filesystem `%s` - host was not defined', $this->fsInfo->getName())
            );
        }

        return \sprintf(
            '%s%s',
            $this->fsInfo->getOption(LocalInfo::HOST_KEY),
            $this->normalizeFilePath($uri)
        );
    }
}
