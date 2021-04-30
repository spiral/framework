<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\Resolver;

use Spiral\Storage\Exception\StorageException;

interface AdapterResolverInterface
{
    /**
     * Build url by provided uri
     *
     * @param string $uri
     * @param array $options any required options can be used
     *
     * @return string|\Stringable
     *
     * @throws StorageException
     */
    public function buildUrl(string $uri, array $options = []);
}
