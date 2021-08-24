<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    /**
     * @var string
     */
    private $domain;

    /**
     * @var UrlSigner
     */
    private $signer;

    /**
     * @var AmazonUriFactory
     */
    private $factory;

    /**
     * @var string|null
     */
    private $prefix;

    /**
     * @param string $keyPairId
     * @param string $privateKey
     * @param string $domain
     * @param string|null $prefix
     */
    public function __construct(string $keyPairId, string $privateKey, string $domain, string $prefix = null)
    {
        $this->assertCloudFrontAvailable();

        $this->domain = $domain;
        $this->prefix = $prefix;
        $this->factory = new AmazonUriFactory();
        $this->signer = new UrlSigner($keyPairId, $privateKey);

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
        $date = $this->getExpirationDateTime($expiration);
        $url = $this->signer->getSignedUrl($this->createUrl($file), $date->getTimestamp());

        return $this->factory->createUri()
            ->withScheme('https')
            ->withHost($this->domain)
            ->withPath($url)
        ;
    }

    /**
     * @return void
     */
    protected function assertCloudFrontAvailable(): void
    {
        if (\class_exists(UrlSigner::class)) {
            return;
        }

        throw new \DomainException('AWS SDK not available. Please install "aws/aws-sdk-php" package');
    }

    /**
     * @param string $file
     * @return string
     */
    private function createUrl(string $file): string
    {
        return \sprintf('https://%s/%s', $this->domain, $this->concat($file, $this->prefix));
    }
}
