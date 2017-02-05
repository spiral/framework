<?php
/**
 * Mongo databases. Attention, configs might include runtime code which depended on environment
 * values only.
 *
 * Attention, this config is pre-placed in application, install spiral/odm in order to use Mongo
 * databases and ODM models.
 *
 * @see MongoConfig
 */

return [
    /*
    * Here you can specify name/alias for database to be treated as default in your application.
    * Such database will be returned from ODM->database(null) call and also can be
    * available using $this->db shared binding.
    */
    'default'   => 'default',

    /*
     * Set of database aliases.
     */
    'aliases'   => [
        'database' => 'default',
        'db'       => 'default',
        'mongo'    => 'default'
    ],

    /*
     * Mongo database configured with connection options.
     */
    'databases' => [
        'default' => [
            'server'   => 'mongodb://localhost:27017',
            'database' => 'spiral-empty',
            'options'  => ['connect' => true]
        ],
        /*{{databases}}*/
    ]
];
