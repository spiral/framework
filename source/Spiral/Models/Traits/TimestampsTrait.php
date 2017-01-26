<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Models\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Timestamps traits adds two magic fields into model/document schema time updated and time created
 * automatically populated when entity being saved. Can be used in Models and Documents.
 *
 * ORM: time_created, time_updated
 * ODM: timeCreated, timeUpdated
 */
trait TimestampsTrait
{
    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    abstract public static function events(): EventDispatcherInterface;
}