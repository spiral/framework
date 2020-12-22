# Spiral Framework: DotEnv Loader
[![Latest Stable Version](https://poser.pugx.org/spiral/dotenv-bridge/version)](https://packagist.org/packages/spiral/dotenv-bridge)
[![Build Status](https://travis-ci.org/spiral/dotenv-bridge.svg?branch=master)](https://travis-ci.org/spiral/dotenv-bridge)
[![Codecov](https://codecov.io/gh/spiral/dotenv-bridge/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/dotenv-bridge/)

## Installation
Mount DotEnv bootloader at top of bootload list in your application:

```php
class App extends Kernel
{
    /*
     * List of components and extensions to be automatically registered
     * within system container on application start.
     */
    protected const LOAD = [
        // Environment configuration
        DotenvBootloader::class,
        
        // ...
    ];
}
```
