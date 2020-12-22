# Spiral Framework: Stempler Adapter
[![Latest Stable Version](https://poser.pugx.org/spiral/stempler-bridge/version)](https://packagist.org/packages/spiral/stempler-bridge)
[![Build Status](https://travis-ci.org/spiral/stempler-bridge.svg?branch=master)](https://travis-ci.org/spiral/stempler-bridge)
[![Codecov](https://codecov.io/gh/spiral/stempler-bridge/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/stempler-bridge/)

## Installation
The extension requires `spiral/views` package.

```
$ composer require spiral/stempler-bridge
```

To enable extension modify your application by adding `Spiral\Stempler\Bootloader\StemplerBootloader`:

```php

class App extends Kernel
{
    /*
     * List of components and extensions to be automatically registered
     * within system container on application start.
     */
    protected const LOAD = [
        // ...
        
        Spiral\Stempler\Bootloader\StemplerBootloader::class,
    ];
}
```

## Usage
You can now use view files via extension `.dark.php`.

```php
<extends:layouts.parent title="My Page"/>
<use:element path="path/element"/>

<block:content>
    <element label="hello world">{{ $variable }}</element>
</block:content>
```