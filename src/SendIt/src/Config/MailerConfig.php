<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\SendIt\Config;

use Spiral\Core\InjectableConfig;

final class MailerConfig extends InjectableConfig
{
    public const CONFIG = 'mailer';

    /** @var array */
    protected $config = [
        'dsn' => '',
        'from' => '',
        'queue' => null,
        'pipeline' => null,
        'queueConnection' => null,
    ];

    public function getDSN(): string
    {
        return $this->config['dsn'];
    }

    public function getFromAddress(): string
    {
        return $this->config['from'];
    }

    /**
     * @deprecated since v2.9.
     */
    public function getQueuePipeline(): ?string
    {
        return $this->getQueue();
    }

    public function getQueue(): ?string
    {
        return $this->config['queue'] ?? $this->config['pipeline'] ?? null;
    }

    public function getQueueConnection(): ?string
    {
        return $this->config['queueConnection'] ?? null;
    }
}
