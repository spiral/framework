Spiral, modular RAD Framework [PHP7]
=======================
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/v/stable)](https://packagist.org/packages/spiral/framework) [![Total Downloads](https://poser.pugx.org/spiral/framework/downloads)](https://packagist.org/packages/spiral/framework) [![License](https://poser.pugx.org/spiral/framework/license)](https://packagist.org/packages/spiral/framework) [![Build Status](https://travis-ci.org/spiral/spiral.svg?branch=master)](https://travis-ci.org/spiral/spiral) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral/spiral/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral/spiral/?branch=master) [![Coverage Status](https://coveralls.io/repos/github/spiral/spiral/badge.svg?branch=09branch)](https://coveralls.io/github/spiral/spiral?branch=09branch)

<img src="https://raw.githubusercontent.com/spiral/guide/master/resources/logo.png" height="170px" alt="Spiral Framework" align="left"/>

The Spiral framework provides open and modular Rapid Application Development (RAD) platform to create applications using an HMVC architecture, layers separation, code re-usability, extremely friendly IoC, PSR-7, simple syntax and customizable scaffolding mechanisms.

[**Skeleton App**](https://github.com/spiral-php/application) | [Guide](https://github.com/spiral-php/guide) | [Twitter](https://twitter.com/spiralphp) | [**Components**](https://github.com/spiral/components) | [Modules](https://github.com/spiral-modules) | [**Contributing**](https://github.com/spiral/guide/blob/master/contributing.md)

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
     * In most cases you don't even need to configure DI to make your
     * application work due autowiring nature of default container.
     *
     * @param Database   $database
     * @param Database   $logs     Can be physical or virtual database
     * @param HttpConfig $config   
     * @return string
     */
    public function indexAction(Database $database, Database $logs, HttpConfig $config)
    {
        dump($config->basePath());
    
        $logs->table('log')->insert(['message' => 'Yo!']);
    
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
class SomeService implements SingletonInterface //or extends Service
{
    private $reader;
    
    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    public function readValue($value)
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
$app->any('/spiral', function ($req, $res, $next) {
    return MySpiralApp::init(...)->http->perform($req, $res);
});
```

StorageManager with deep PSR-7 streams integration:

```php
public function downloadAction()
{
    return $this->responses->attachment(
        $this->storage->open('cloud:filename.txt'), 
        'filename.txt'
    );
}
```

ORM with adaptive scaffolding/migrations for MySQL, PostgresSQL, SQLite, SQLServer:

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
        
        //Simple relation definition
        'comments' => [self::HAS_MANY => Comment::class],
        
        //Relation thought interface
        'author'   => [
            self::BELONGS_TO    => AuthorInterface::class,
            self::LATEP_BINDING => true
        ],
        
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
    ];
}
```

```php
$posts = $this->orm->source(Post::class)
    ->find()->distinct()
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
$post->publish_at = 'tomorrow';
$post->author = new User(['name' => 'Antony']);

$post->tags->link(new Tag(['name' => 'tag A']));
$post->tags->link($tags->findOne(['name' => 'tag B']));

$transaction = new Transaction();
$transaction->store($post);
$transaction->run();

dump($post);
```

Embedded functionality for static indexation of your code:

```php
public function indexAction(ClassLocatorInterface $locator, InvocationLocatorInterface $invocations)
{
    dump($locator->getClasses(ControllerInterface::class));
    dump($invocations->getInvocations(new \ReflectionFunction('dump')));
}
```

Extendable and programmable template markup language compatible with any command syntax ([plain PHP by default](https://github.com/spiral/spiral/issues/125)):

```html
<spiral:grid source="<?= $uploads ?>" as="upload">
    <grid:cell title="ID:" value="<?= $upload->getId() ?>"/>
    <grid:cell title="Time Created:" value="<?= $upload->getTimeCreated() ?>"/>
    <grid:cell title="Label:" value="<?= e($upload->getLabel()) ?>"/>

    <grid:cell.bytes title="Filesize:" value="<?= $upload->getFilesize() ?>"/>

    <grid:cell>
        <a href="<?= uri('uploads::edit', $upload) ?>">Edit</a>
    </grid:cell>
</spiral:grid>
```
> You can write your own virtual tags (similar to web components or [Polymer](https://www.polymer-project.org/1.0/) with server side compilation), layouts and wrappers with almost any functionality or connect external libraries like [Vault](https://github.com/spiral-modules/vault).

Includes
=============
Plug and Play extensions, small footprint, IDE friendly, frontend toolkit (ajax forms, asset manager), static analysis, loosely coupled, cloud storages, MongoDB, auto-indexable translator, Interop Container, Zend Diactoros, Symfony Console, Symfony Translation (interfaces), Symfony Events, Monolog, Twig, debugging/profiling tools and much more.

Modules
=======
[Scaffolder](https://github.com/spiral-modules/scaffolder) - provides set of console commands and extendable class declarations for application scaffolding.

[Vault](https://github.com/spiral-modules/vault) - friendly and extendable administration panel based on Materialize CSS and Security component.

[Auth](https://github.com/spiral-modules/auth) - authentication layer with multiple token operators and firewall middlewares.

Inspired by
===========
Laravel 5+, CodeIgniter, Yii 2, Symfony 2, ASP.NET 3, RoR ORM.
