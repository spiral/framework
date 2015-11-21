<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules\Interfaces;

use Spiral\Modules\PublisherInterface;

/**
 * Module publishes resources.
 */
interface PublishesInterface
{
    /**
     * Must publish needed resources.
     *
     * @param PublisherInterface $publisher
     */
    public function publish(PublisherInterface $publisher);
}