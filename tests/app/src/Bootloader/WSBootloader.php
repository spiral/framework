<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Http\WebsocketsBootloader;

class WSBootloader extends Bootloader
{
    protected const DEPENDENCIES = [WebsocketsBootloader::class];

    public function boot(WebsocketsBootloader $ws, EnvironmentInterface $env): void
    {
        if ($env->get('RR_BROADCAST_PATH') === null) {
            return;
        }

        $ws->setServerCallback($env->get('WS_SERVER_CALLBACK'));

        if ($env->get('WS_TOPIC_CALLBACK') !== null) {
            $ws->addTopicCallback('topic', $env->get('WS_TOPIC_CALLBACK'));
        }

        if ($env->get('WS_TOPIC_WILDCARD_CALLBACK') !== null) {
            $ws->addTopicCallback('wildcard.{id}', $env->get('WS_TOPIC_WILDCARD_CALLBACK'));
        }
    }
}
