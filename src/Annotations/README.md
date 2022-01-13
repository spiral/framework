# Automatic Annotation Discovery
[![Latest Stable Version](https://poser.pugx.org/spiral/annotations/version)](https://packagist.org/packages/spiral/annotations)
[![Build Status](https://travis-ci.org/spiral/annotations.svg?branch=master)](https://travis-ci.org/spiral/annotations)
[![Codecov](https://codecov.io/gh/spiral/annotations/graph/badge.svg)](https://codecov.io/gh/spiral/annotations)

## Package is deprecated. Please, use `spiral/attributes` instead

## Installation
Service does not require any bootloader and can be enabled in spiral application with simple composer dependency.

```bash
$ composer require spiral/annotations 
```

## Example
To find all annotated classes:

```php
use Spiral\Annotations;

$locator = new Annotations\AnnotationLocator($classLocator); 

foreach($locator->findClasses(MyAnnotation::class) as $class) {
    dump($class->getClass());
    dump($class->getAnnotation());
} 
```