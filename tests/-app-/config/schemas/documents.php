<?php
/**
 * This part of configuration is responsible for ODM mapping options, including set of mutators
 * to be automatically attached to different field types. Attention, configs might include runtime
 * code which depended on environment values only.
 *
 * @see SchemasConfig
 */
use Spiral\Models\Accessors;
use Spiral\ODM\Accessors as ODMAccessors;
use Spiral\ODM\ODM;

return [
    /*
     * Set of mutators to be applied for specific field types.
     */
    'mutators' => [
        'int'      => ['setter' => 'intval'],
        'float'    => ['setter' => 'floatval'],
        'string'   => ['setter' => 'strval'],
        'bool'     => ['setter' => 'boolval'],

        //Automatic casting of mongoID
        'ObjectID' => ['setter' => [ODM::class, 'mongoID']],

        'array::string'    => ['accessor' => ODMAccessors\StringArray::class],
        'array::objectIDs' => ['accessor' => ODMAccessors\ObjectIDsArray::class],
        'array::integer'   => ['accessor' => ODMAccessors\IntegerArray::class],

        'timestamp' => ['accessor' => Accessors\UTCMongoTimestamp::class],
        /*{{mutators}}*/
    ],
    /*
     * Mutator aliases can be used to declare custom getter and setter filter methods.
     */
    'aliases'  => [
        //Id aliases
        'MongoId'                        => 'ObjectID',
        'objectID'                       => 'ObjectID',
        \MongoDB\BSON\ObjectID::class    => 'ObjectID',

        //Timestamps
        \MongoDB\BSON\UTCDateTime::class => 'timestamp',

        //Scalar typ aliases
        'integer'                        => 'int',
        'long'                           => 'int',
        'text'                           => 'string',

        //Array aliases
        'array::int'                     => 'array::integer',
        'array::MongoId'                 => 'array::objectIDs',
        'array::ObjectID'                => 'array::objectIDs',
        'array::MongoDB\BSON\ObjectID'   => 'array::objectIDs'

        /*{{mutators.aliases}}*/
    ]
];

