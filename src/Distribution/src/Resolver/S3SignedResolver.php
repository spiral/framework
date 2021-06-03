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
     * @param S3ClientInterface $client
     * @param string $bucket
     */
    public function __construct(S3ClientInterface $client, string $bucket)
    {
        $this->client = $client;
        $this->bucket = $bucket;

        parent::__construct();
    }

    /**
     * @param string $file
     * @param DateIntervalFormat|null $expiration
     * @return UriInterface
     * @throws \Exception
     */
    public function resolve(string $file, $expiration = null): UriInterface
    {
        $command = $this->createCommand($file);

        $request = $this->client->createPresignedRequest(
            $command,
            $this->getExpirationDateTime($expiration)
        );

        return $request->getUri();
    }

    /**
     * @param string $file
     * @return CommandInterface
     */
    private function createCommand(string $file): CommandInterface
    {
        return $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key'    => $file,
        ]);
    }
}
