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

    /**
     * @return string
     */
    public function getDSN(): string
    {
        return $this->config['dsn'];
    }

    /**
     * @return string
     */
    public function getFromAddress(): string
    {
        return $this->config['from'];
    }

    /**
     * @return string
     */
    public function getQueuePipeline(): string
    {
        return $this->config['pipeline'];
    }
}
