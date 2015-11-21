<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Psr\Log\LoggerAwareInterface;
use Spiral\Modules\Interfaces\PublishesInterface;

/**
 * Responsible for publishing module files and configs.
 */
interface PublisherInterface extends LoggerAwareInterface
{
    /**
     * Internal ModuleManager agreement for better debugging, must ONLY call:
     *
     * $publishable->publish($this).
     *
     * @param PublishesInterface $publishable
     */
    public function publish(PublishesInterface $publishable);
}