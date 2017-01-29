<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Models;

use Spiral\Models\Accessors\UTCMongoTimestamp;
use Spiral\Tests\BaseTest;
use TestApplication\Database\SampleDocument;

class MongoTimestampsTest extends BaseTest
{
    public function testMongoAccessor()
    {
        $this->configureDB();

        $document = new SampleDocument();

        $document->time_altered = 'now';
        $this->assertInstanceOf(UTCMongoTimestamp::class, $document->time_altered);
        $this->assertNotEmpty((string)$document->time_altered);

        $document->save();

        $this->assertInstanceOf(UTCMongoTimestamp::class, $document->timeCreated);
        $this->assertNotEmpty((string)$document->timeCreated);
        $this->assertInstanceOf(UTCMongoTimestamp::class, $document->timeUpdated);
        $this->assertNotEmpty((string)$document->timeUpdated);

        $document->touch()->save();

        $documentB = SampleDocument::findByPK($document->_id);

        $this->assertInstanceOf(UTCMongoTimestamp::class, $documentB->time_altered);
        $this->assertNotEmpty((string)$documentB->time_altered);

        $this->assertInstanceOf(UTCMongoTimestamp::class, $documentB->timeCreated);
        $this->assertNotEmpty((string)$documentB->timeCreated);
        $this->assertInstanceOf(UTCMongoTimestamp::class, $documentB->timeUpdated);
        $this->assertNotEmpty((string)$documentB->timeUpdated);

        $this->assertInternalType('array', $document->time_altered->__debugInfo());

    }

    protected function configureDB()
    {
        $this->console->run('odm:schema')->getOutput()->fetch();
    }
}