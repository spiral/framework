Spiral PHP Framework, Current Status
=======================
[![Build Status](https://travis-ci.org/spiral-php/spiral.svg?branch=master)](https://travis-ci.org/spiral-php/spiral)

The Spiral framework provides a Rapid Application Development (RAD) platform to develop software applications 
using an MVC architecture, using a simple but strong syntax similar to other popular frameworks.

Spiral was originally built in 2009 for internal projects and has been continuous updated over the last
 few years. In 2015, I decided to open source the framework so that developers had a better way to build
  custom applications quickly.  

At the moment, I'm still transitioning some of the old codebase into the new architecture.

What is ready
=================
Here is a list of features, which were successfully moved to the new core. At the moment, no tests 
have been created.

DBAL (+ migrations)
-------------
Currently, you can use the DBAL with 4 different databases (MySQL, PostgresSQL, SQLite, SQLServer).
The database schema has building and reading abilities, query builders, etc. 

The DBAL builder is built on the principals of a two directional database schema reading, which allows 
you to not only create database migrations, but to analyze existing table schemas (this functionality
is used in ORM).

IoC
-------------
The Core includes a build-in container with set of component bindings. Additionally Spiral has 
"controllable injection" where you can request from the factory what specific version of instance you
need in your controller method (method injection) or any constuctor (constructor injection). This idea
was implemented for dbal databases, mongo databases, storage containers, cache stores (by class name), 
and redis clients.

Example:
```php
public function action(Database $default, Database $postgres, ..., MemcacheStore $memcache, ...)
{
    dump($default);
}
```

ODM
-------------
Mongo databases and odm documents are moved without any problems.

Views
-------------
View component and all processors moved without any problems. Html Composer (templater) works without
any problems. However, tests should be updated.

Storage
-------------
The storage component was moved without many changes. It currently works with Amazon, Rackspace, FTP
and local storages using prefixes to detect location.

Localization
-------------
Localization has been completed, which includes component traits, views, pluralization, etc.

Console and Console Commands
-------------
Old code was wiped clean and switched to Symfony classes. Command to configure application, install 
modules, perform migrations and update ODM schema (plus UML export) are all ready.

Modules
-------------
Modules are working well. This includes bindings, installers, and resources. Check sample modules 
spiral-toolkit and spiral-markdown. Profiler has not been updated.

Image
-------------
ImageObject works well, but currently there is only one image processor (which we have been using for
4 years) - ImageMagic via console interface.

ORM
=================
Currently in development (will take week or two).

What is NOT ready
=================
Currently I'm in the middle of planning new HttpDispatcher realization. Even though we already had
Routes, Requests and Responses in a previous generation, I would like to consider implementing PSR7
and middleware(s). Based on this plan of action, the following pieces are not ready:

Paginator
-------------
The entire pagination class is ready with its' interfaces and traits. However, it currently can't receive
a page number from a request as there isn't a request :)

Session, Cookies and CRSF tokens
-------------
This will require some tweaking to fit into HttpDispatcher middleware.

Profiler module
-------------
Profiler was originally handling the dispatcher::response event. There is no such event at the moment
and profiler doesn't function well.
