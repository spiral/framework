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
        'dsn'      => '',
        'from'     => '',
        'pipeline' => '',
    ];

    public function getDSN(): string
    {
        return $this->config['dsn'];
    }

    public function getFromAddress(): string
    {
        return $this->config['from'];
    }

    public function getQueuePipeline(): string
    {
        return $this->config['pipeline'];
    }
}
