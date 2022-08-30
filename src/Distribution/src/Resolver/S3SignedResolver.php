<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Distribution\Resolver;

use Aws\CommandInterface;
use Aws\S3\S3ClientInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Distribution\Internal\DateTimeIntervalFactoryInterface;

/**
 * @psalm-import-type DateIntervalFormat from DateTimeIntervalFactoryInterface
 * @see DateTimeIntervalFactoryInterface
 */
class S3SignedResolver extends ExpirationAwareResolver
{
    /**
     * @var S3ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $bucket;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @param string|null $prefix
     */
    public function __construct(S3ClientInterface $client, string $bucket, string $prefix = null)
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->prefix = $prefix;

        parent::__construct();
    }

    /**
     * @param DateIntervalFormat|null $expiration
     * @throws \Exception
     */
    public function resolve(string $file, $expiration = null): UriInterface
    {
        $command = $this->createCommand($this->concat($file, $this->prefix));

        $request = $this->client->createPresignedRequest(
            $command,
            $this->getExpirationDateTime($expiration)
        );

        return $request->getUri();
    }

    private function createCommand(string $file): CommandInterface
    {
        return $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $file,
        ]);
    }
}
