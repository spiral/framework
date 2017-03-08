Spiral, modular RAD Framework
=======================
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/v/stable)](https://packagist.org/packages/spiral/framework) [![Total Downloads](https://poser.pugx.org/spiral/framework/downloads)](https://packagist.org/packages/spiral/framework) [![License](https://poser.pugx.org/spiral/framework/license)](https://packagist.org/packages/spiral/framework) [![Build Status](https://travis-ci.org/spiral/spiral.svg?branch=master)](https://travis-ci.org/spiral/spiral) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral/spiral/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral/spiral/?branch=master) [![Coverage Status](https://coveralls.io/repos/github/spiral/spiral/badge.svg?branch=master)](https://coveralls.io/github/spiral/spiral?branch=master)

<img src="https://raw.githubusercontent.com/spiral/guide/master/resources/logo.png" height="170px" alt="Spiral Framework" align="left"/>

The Spiral framework provides open and modular Rapid Application Development (RAD) platform to create applications using domain driven architecture, layers separation, code re-usability, extremely friendly [IoC](https://github.com/container-interop/container-interop), PSR-7, simple syntax and customizable scaffolding mechanisms. 

<b>[Skeleton App](https://github.com/spiral-php/application)</b> | [Guide](https://github.com/spiral-php/guide) | [Twitter](https://twitter.com/spiralphp) | [Modules](https://github.com/spiral-modules) | [CHANGELOG](/CHANGELOG.md) | [Contributing](https://github.com/spiral/guide/blob/master/contributing.md)

<br/><br/>

Examples:
--------

```php
class HomeController extends Controller
{
    /**
     * DI can automatically deside what database/cache/storage
     * instance to provide for every action parameter based on it's 
     * name or type.
     *
     * @param Database   $database
     * @param Database   $logs     Can be physical or virtual database
     * @param HttpConfig $config   
     * @return string
     */
    public function indexAction(Database $database, Database $logs, HttpConfig $config)
    {
        dump($config->basePath());
    
        $logs->table('log')->insertOne(['message' => 'Yo!']);
    
        return $this->views->render('welcome', [
            'users' => $database->table('users')->select()->where(['name' => 'John'])->all()
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
        ReaderInterface::class => [self::class, 'reader'],
    ];
    
    protected function reader(ParserInterface $parser, Database $database)
    {
        return new Reader($parser, $database->table('some'));
    }
}
```

Declarative/lazy singletons and services:

```php
class SomeService implements SingletonInterface
{
    private $reader;
    
    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    public function readValue(string $value)
    {
        return $this->reader->read($value);
    }
}
```

JSON responses, method injections, [IoC scopes](https://raw.githubusercontent.com/spiral/guide/master/resources/scopes.png), container shortcuts:

```php
public function indexAction(ServerRequestInterface $request, SomeService $service)
{
    dump($service->readValue('abc'));
    
    //Shortcuts
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

ORM with scaffolding/migrations for MySQL, PostgresSQL, SQLite, SQLServer:

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
        
        //Relation thought interface
        'author'   => [
            self::BELONGS_TO   => AuthorInterface::class,
            self::LATE_BINDING => true
        ],
        
        //Hybrid databases
        'metadata' => [
            Document::ONE => Mongo\Metadata::class
        ]
    ];
}
```

```php
$posts = $postSource->find()->distinct()
    ->with('comments', ['where' => ['{@}.approved' => true]]) //Automatic joins
    ->with('author')->where('author_name', 'LIKE', $authorName) //Fluent
    ->load('comments.author') //Cascade eager-loading (joins or external query)
    ->paginate(10) //Quick pagination using active request
    ->fetchAll();

foreach($posts as $post) {
    echo $post->author->getName();
}
```

```php
$post = new Post();
$post->publish_at = 'tomorrow 8am';
$post->author = new User(['name' => 'Antony']);

$post->tags->link(new Tag(['name' => 'tag A']));
$post->tags->link($tags->findOne(['name' => 'tag B']));

$transaction = new Transaction();
$transaction->store($post);
$transaction->run();

dump($post);
```

Tests
-----
```
composer install
phpunit
```
