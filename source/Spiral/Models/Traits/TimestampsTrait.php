<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Models\Traits;

use Spiral\Events\Entities\Event;
use Spiral\Events\Entities\ObjectEvent;
use Spiral\Models\DataEntity;
use Spiral\ODM\Document;
use Spiral\ODM\Entities\Schemas\DocumentSchema;
use Spiral\ORM\Entities\Schemas\RecordSchema;
use Spiral\ORM\Record;

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
     * Touch object and update it's time_updated value.
     *
     * @return $this
     */
    public function touch()
    {
        if ($this instanceof Record) {
            $this->setField('time_updated', new \DateTime(), false);
        } elseif ($this instanceof Document) {
            $this->setField('timeUpdated', new \MongoDate(time()), false);
        }

        return $this;
    }

    /**
     * @param bool $analysis DataEntity is being analyzed.
     */
    protected static function initTimestampsTrait($analysis)
    {
        $dispatcher = self::events();

        if ($analysis) {
            //To modify schema
            $schemaListener = self::__timestampsSchema();
            if (!$dispatcher->hasListener('describe', $schemaListener)) {
                $dispatcher->listen('describe', $schemaListener);
            }

            return;
        }

        $saveListener = self::__timestampsSave();
        $dispatcher->listen('saving', $saveListener);
        $dispatcher->listen('updating', $saveListener);
    }

    /**
     * DataEntity save.
     *
     * @return \Closure
     */
    private static function __timestampsSave()
    {
        return function (ObjectEvent $event) {
            /**
             * @var DataEntity $model
             */
            $model = $event->parent();
            if ($model instanceof Record) {
                switch ($event->name()) {
                    case 'saving':
                        //There is no break statement missing
                        $model->setField('time_created', new \DateTime(), false);
                    case 'updating':
                        $model->setField('time_updated', new \DateTime(), false);
                }
            }

            if ($model instanceof Document) {
                switch ($event->name()) {
                    case 'saving':
                        //There is no break statement missing
                        $model->setField('timeCreated', new \MongoDate(time()), false);
                    case 'updating':
                        $model->setField('timeUpdated', new \MongoDate(time()), false);
                }
            }
        };
    }

    /**
     * Create appropriate schema modification listener.
     *
     * @return callable
     */
    private static function __timestampsSchema()
    {
        return function (Event $event) {
            if ($event->context()['property'] == 'schema') {
                //Registering fields in schema
                switch (get_class($event->context()['schema'])) {
                    case RecordSchema::class:
                        $event->context()['value']['time_created'] = 'timestamp,null';
                        $event->context()['value']['time_updated'] = 'timestamp,null';
                        break;
                    case DocumentSchema::class:
                        $event->context()['value']['timeCreated'] = 'timestamp';
                        $event->context()['value']['timeUpdated'] = 'timestamp';
                        break;
                }
            }
        };
    }
}