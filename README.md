Spiral, PSR7/PHP7 Framework
=======================
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/v/stable)](https://packagist.org/packages/spiral/framework) [![Total Downloads](https://poser.pugx.org/spiral/framework/downloads)](https://packagist.org/packages/spiral/framework) [![License](https://poser.pugx.org/spiral/framework/license)](https://packagist.org/packages/spiral/framework) [![Build Status](https://travis-ci.org/spiral/framework.svg?branch=master)](https://travis-ci.org/spiral/spiral) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral/spiral/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral/spiral/?branch=master) [![Coverage Status](https://coveralls.io/repos/github/spiral/spiral/badge.svg?branch=develop)](https://coveralls.io/github/spiral/spiral?branch=develop)

<img src="https://raw.githubusercontent.com/spiral/guide/master/resources/logo.png" height="170px" alt="Spiral Framework" align="left"/>

The Spiral framework provides open and modular Rapid Application Development (RAD) environment, powerful Database tools, code re-usability, extremely friendly [IoC](https://github.com/container-interop/container-interop), IDE integration, PSR-\* standards, simple syntax and customizable scaffolding mechanisms. 


<b>[Skeleton App](https://github.com/spiral-php/application)</b> | [Guide](https://github.com/spiral-php/guide) | [Twitter](https://twitter.com/spiralphp) | [Modules](https://github.com/spiral-modules) | [CHANGELOG](/CHANGELOG.md) | [Contributing](https://github.com/spiral/guide/blob/master/contributing.md) | [Forum](https://groups.google.com/forum/#!forum/spiral-framework) | [Video Tutorials](https://www.youtube.com/watch?v=zJ4fqW4M86I&list=PLHZNig4c1SXGVt8hUVHxZTrlJqqdn8ktW)

<br/><br/>

Installation
------------
```
composer create-project spiral/application
cd application
```

Once application installed you can ensure that it was configured properly by executing:

```
./spiral configure -k && vendor/bin/phpunit
```

Examples:
--------

```php
class HomeController extends Controller
{
    public function indexAction(Database $database, Database $logs, HttpConfig $config): string 
    {
        dump($config->basePath());
    
        $logs->table('log')->insertOne(['message' => 'Yo!']);
    
        return $this->views->render('welcome', [
            'users' => $database->table('users')->select()->where(['name' => 'John'])->fetchAll()
        ]);
    }
}
```

Bootloaders, Factory Methods:

```php
class MyBootloader extends Bootloader
{
    const BINDINGS = [
        ParserInterface::class => DefaultParser::class,
        'someService'          => SomeService::class
    ];
    
    const SINGLETONS = [
        ReaderInterface::class => [self::class, 'makeReader'],
    ];
    
    protected function makeReader(ParserInterface $parser, Database $database): Reader
    {
        return new Reader($parser, $database->table('some'));
    }
}
```

JSON responses, method injections, [IoC scopes](https://raw.githubusercontent.com/spiral/guide/master/resources/scopes.png), container shortcuts, IDE helpers:

```php
public function indexAction(ServerRequestInterface $request, SomeService $service): array
{    
    dump($this->someService === $service);
    
    return [
        'status' => 200,
        'uri'    => (string)$request->getUri()
    ];
}
```

![Short Bindings](https://raw.githubusercontent.com/spiral/guide/master/resources/virtual-bindings.gif)

Spiral application(s) can be used as middleware/endpoint inside other PSR7 frameworks:

```php
use Zend\Diactoros\Server;
use Zend\Expressive\Application;
use Zend\Stratigility\MiddlewarePipe;

$app = new Application();
$app->any('/spiral', SpiralApp::init(...)->http);
```

ORM with scaffolding/migrations for MySQL, PostgreSQL, SQLite, SQL Server:

```php
class Post extends RecordEntity
{
    use TimestampsTrait;

    //Database partitions, isolation and aliasing
    const DATABASE = 'blog';

    const SCHEMA = [
        'id'     => 'bigPrimary',
        'title'  => 'string(64)',
        'status' => 'enum(published,draft)',
        'body'   => 'text',
        
        //Simple relation definitions
        'comments' => [self::HAS_MANY => Comment::class],
        
        //Not very simple relation definitions
        'collaborators' => [
            self::MANY_TO_MANY  => User::class,
            self::PIVOT_TABLE   => 'post_collaborators_map',
            self::PIVOT_COLUMNS => [
                'time_assigned' => 'datetime',
                'type'          => 'string, nullable',
            ],
            User::INVERSE       => 'collaborated_posts'
        ],
        
        //Statically binded relations
        'author'   => [
            self::BELONGS_TO   => AuthorInterface::class,
            self::LATE_BINDING => true
        ],
               
        //Hybrid databases
        'metadata' => [Document::ONE => Mongo\Metadata::class]
    ];
}
```

```php
$posts = $postSource->find()->distinct()
    ->with('comments', ['where' => ['{@}.approved' => true]]) //Automatic joins
    ->with('author')->where('author_name', 'LIKE', $authorName) //Fluent
    ->load('comments.author') //Cascade eager-loading (joins or external query)
    ->paginate(10) //Quick pagination using active request
    ->getIterator();

foreach ($posts as $post) {
    echo $post->author->getName();
}
```

```php
$post = new Post();
$post->publish_at = 'tomorrow 8am';
$post->author = new User(['name' => 'Antony']);

$post->tags->link(new Tag(['name' => 'tag A']));
$post->tags->link($tags->findOne(['name' => 'tag B']));

// automatic transaction management
$post->save();

// or: transactional approach
$transaction->store($post);
```

And much more: <b>[Skeleton App](https://github.com/spiral-php/application)</b> | [Guide](https://github.com/spiral-php/guide)

Tests
-----
```
$ composer install
$ vendor/bin/phpunit
```
