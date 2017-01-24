<?php
/**
 * EncrypterManager component configuration file. Attention, configs might include runtime code
 * which depended on environment values only.
 *
 * @see EncrypterConfig
 */

return [
    /*
     * Encryption key can be found in .env file. You can generate new encryption key via console
     * command "app:key".
     */
    'key'    => getenv('SPIRAL_KEY'),
];
