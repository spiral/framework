Static Analysis: Class and Invocation locators
================================
[![Latest Stable Version](https://poser.pugx.org/spiral/tokenizer/version)](https://packagist.org/packages/spiral/tokenizer)
[![Build Status](https://travis-ci.org/spiral/tokenizer.svg?branch=master)](https://travis-ci.org/spiral/tokenizer)
[![Codecov](https://codecov.io/gh/spiral/tokenizer/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/tokenizer/)

The package provides the ability to locate all instances of desired classes, function/method invocations.

Example:
--------
```php
$cl = new ClassLocator((new Finder())->files()->in([__DIR__]));

print_r($cl->getClasses(TargetInterface::class));
```

License:
--------
The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained by [SpiralScout](https://spiralscout.com).