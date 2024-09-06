<?php

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
    public function __construct(
        private readonly S3ClientInterface $client,
        private readonly string $bucket,
        private readonly ?string $prefix = null
    ) {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function resolve(string $file, mixed $expiration = null): UriInterface
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
