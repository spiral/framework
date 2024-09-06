<?php

declare(strict_types=1);

namespace Spiral\Distribution\Resolver;

use Aws\CloudFront\UrlSigner;
use Psr\Http\Message\UriInterface;
use Spiral\Distribution\Internal\AmazonUriFactory;
use Spiral\Distribution\Internal\DateTimeIntervalFactoryInterface;

/**
 * Amazon CloudFront is a content delivery network (CDN) service.
 *
 * @psalm-import-type DateIntervalFormat from DateTimeIntervalFactoryInterface
 * @see DateTimeIntervalFactoryInterface
 */
class CloudFrontResolver extends ExpirationAwareResolver
{
    private readonly UrlSigner $signer;
    private readonly AmazonUriFactory $factory;

    public function __construct(
        string $keyPairId,
        string $privateKey,
        private readonly string $domain,
        private readonly ?string $prefix = null
    ) {
        $this->assertCloudFrontAvailable();
        $this->factory = new AmazonUriFactory();
        $this->signer = new UrlSigner($keyPairId, $privateKey);

        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function resolve(string $file, mixed $expiration = null): UriInterface
    {
        $date = $this->getExpirationDateTime($expiration);
        $url = $this->signer->getSignedUrl($this->createUrl($file), $date->getTimestamp());

        return $this->factory->createUri($url);
    }

    protected function assertCloudFrontAvailable(): void
    {
        if (\class_exists(UrlSigner::class)) {
            return;
        }

        throw new \DomainException('AWS SDK not available. Please install "aws/aws-sdk-php" package');
    }

    private function createUrl(string $file): string
    {
        return \sprintf('https://%s/%s', $this->domain, $this->concat($file, $this->prefix));
    }
}
