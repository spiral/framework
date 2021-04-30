# Storage Engine
Storage engine provides required functionality to manage your files for different file servers 
based on provided configuration.  

Storage engine is based on [FlySystem](https://github.com/thephpleague/flysystem) and for correct work with file servers 
(except local one) you will need to provide required FlySystem adapter (take a look at concrete filesystem doc).

StorageEngine based on 2 basic interfaces:
1. [StorageInterface](doc/StorageInterface.md)
    * works with all supported file servers that handle files
    * works with file paths in a specific format (`{serverName}://{filePath}` by default)
      * to change it you can prepare your own `\Spiral\StorageEngine\Parser\UriParser` class and make required binding
2. [ResolveManagerInterface](doc/ResolveManagerInterface.md)
    * build url (and urls list) for file download

## Supported file servers
Current release provides ability to work with:
- [Local filesystem](doc/local.md)
- [Aws S3 (+async)](doc/awsS3.md)

## Configuration
You can configure file servers usage in Spiral with configuration file `storage.php` located in configuration directory.

You can receive more details about spiral configuration from [here](https://spiral.dev/docs/start-configuration).

More details about specific file servers configuration you can find [here](#supported-file-servers)

Configuration can contains:
- servers (required)
  - file servers description
- buckets (required)
  - server buckets description
  - main items for StorageEngine work. When you are working with filesystems from StorageEngine you are working with buckets.
  - bucket info refers to server and has only specific options 
    - `directory` is relative to root directory for local file server
    - `bucket` is aws servers bucket name
- tmp-dir
  - temp directory for creating some temp files for process
  - system temp directory by default 

### Configuration example
```php
<?php

declare(strict_types=1);

$s3Client = new \Aws\S3\S3Client([
    'version' => 'latest',
    'region' => env('AWS_REGION'),
    'credentials' => new \Aws\Credentials\Credentials(env('AWS_KEY'), env('AWS_SECRET')),
    'use_path_style_endpoint' => true,
    'endpoint' => env('AWS_PUBLIC_URL')
]);

return [
    'servers' => [
        'awsServer' => [
            'adapter' => \League\Flysystem\AwsS3V3\AwsS3V3Adapter::class,
            'options' => [
                'client' => $s3Client,
            ]
        ],
        'localServer' => [
            'adapter' => \League\Flysystem\Local\LocalFilesystemAdapter::class,
            'options' => [
                'rootDir' => '/var/www/tmpLocal',
                'host' => 'http://localhost:8115/public/',
            ],
        ],
    ],
    'buckets' => [
        'local' => [
            'server' => 'local',
            'options' => [
                'directory' => 'b1/',
            ],
        ],
        'aws' => [
            'server' => 'aws',
            'options' => [
                'bucket' => env('AWS_BUCKET'),
            ],
        ],
        'aws2' => [
            'server' => 'aws',
            'options' => [
                'bucket' => env('AWS_BUCKET2'),
            ]
        ],
    ],
    'tmp-dir' => '/var/www/tmp/',
];

```

## Basic usage
### If you are use Spiral Framework
When you finish your configuration file you should add `Spiral\StorageEngine\Bootloader\StorageEngineBootloader` in your app.

### Usage
When you need to make some file operation you should use your StorageEngine object for it:
1. To perform different operations on your files you can use FilesystemOperator implemented object:
``` php
/** @var \Spiral\StorageEngine\StorageEngine $storageEngine **/
$uri = $storageEngine->write('local', 'someDir/myFile.txt', 'It is my text'); // = 'local://someDir/myFile.txt'
$streamContent = $storage->readStream($uri);
$fileSize = $storage->fileSize($uri);

$copiedUri = $storageEngine->copy($uri, 'aws', 'myCopy.txt'); // = 'aws://myCopy.txt'
$mimeType = $storageEngine->mimeType($copiedUri); // = 'text/plain'
```
2. To build url to your file you can use ResolveManagerInterface object:
``` php
$resolveManager->buildUrl('local://someDir/myFile.txt'); // for example it can return smth like 'http://myhost.com/files/somedir/myfile.txt'
```
* P.S. For local server you should define host in server description to build url. In other case it will throw exception.

## License:
MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [Spiral Scout](https://spiralscout.com).
