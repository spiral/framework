Spiral, modular RAD Framework (beta)
=======================
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/v/stable)](https://packagist.org/packages/spiral/framework) [![Total Downloads](https://poser.pugx.org/spiral/framework/downloads)](https://packagist.org/packages/spiral/framework) [![License](https://poser.pugx.org/spiral/framework/license)](https://packagist.org/packages/spiral/framework) [![Build Status](https://travis-ci.org/spiral/spiral.svg?branch=master)](https://travis-ci.org/spiral/spiral) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiral/spiral/badges/quality-score.png)](https://scrutinizer-ci.com/g/spiral/spiral/?branch=master) [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/spiral/hotline)

<img src="https://raw.githubusercontent.com/spiral/guide/master/resources/logo.png" height="220px" alt="Spiral Framework" align="left"/>

The Spiral framework provides open and modular Rapid Application Development (RAD) platform to create applications using an HMVC architecture, layers separation, code re-usability, extremely friendly IoC, PSR-7, simple syntax and customizable scaffolding mechanisms.

[**Skeleton App**](https://github.com/spiral-php/application) | [Guide](https://github.com/spiral-php/guide) | [Gitter](https://gitter.im/spiral/hotline) | [**Forum**](https://groups.google.com/forum/#!forum/spiral-framework) | [Twitter](https://twitter.com/spiralphp) | [**Components**](https://github.com/spiral/components) | [Modules](https://github.com/spiral-modules) | [**Contributing**](https://github.com/spiral/guide/blob/master/contributing.md)

<br/><br/><br/>

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
    protected $bindings = [
        ParserInterface::class => DefaultParser::class,
        'someService'          => SomeService::class
    ];
    
    protected $singletons = [
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

ORM with adaptive scaffolding (optional) for MySQL, PostgresSQL, SQLite, SQLServer:

```php
class Post extends Record //or RecordEntity without active record like methods
{
    use TimestampsTrait;

    //Database partitions, isolation and aliasing
    protected $database = 'blog';

    protected $schema = [
        'id'     => 'bigPrimary',
        'title'  => 'string(64)',
        'status' => 'enum(published,draft)',
        'body'   => 'text',
        
        //Simple relation definition
        'author'   => [self::BELONGS_TO => Author::class],
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
    ];
}
```

```php
//Post::find() == $this->orm->selector(Post::class) == PostSource->find() == Post::source()->find()
$posts = Post::find()
    ->distinct()
    ->with('comments') //Automatic joins
    ->with('author')->where('author.name', 'LIKE', $authorName) //Fluent
    ->load('comments.author') //Cascade eager-loading (joins or external query)
    ->paginate(10) //Quick pagination using active request
    ->all();

foreach($posts as $post) {
    echo $post->author->getName();
}
```

Embedded functionality for static indexation of your code (foundation for many internal components):

```php
public function indexAction(ClassLocatorInterface $locator, InvocationLocatorInterface $invocations)
{
    dump($locator->getClasses(ControllerInterface::class));
}
```

Extendable and programmable template engine compatible with any command syntax ([plain PHP by default](https://github.com/spiral/spiral/issues/125)):

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
Plug and Play extensions, small footprint, IDE friendly, frontend toolkit (ajax forms, asset manager), cache and logic cache, static analysis, , cloud storages, auto-indexable translator, Interop Container, Zend Diactoros, Symfony Console, Symfony Translation (interfaces), Symfony Events, Monolog, Twig, debugging/profiling tools and much more.

Modules
=======
[Scaffolder](https://github.com/spiral-modules/scaffolder) - provides set of console commands and extendable class declarations for application scaffolding.

[Security Layer](https://github.com/spiral-modules/security) - flat RBAC security layer with Role-Permission-Rule association mechanism. 

[Vault](https://github.com/spiral-modules/vault) - friendly and extendable administration panel based on Materialize CSS and Security component.

[Auth](https://github.com/spiral-modules/auth) - authentication layer with multiple token operators and firewall middlewares.

Roadmap
=======
- Queue module
- SwiftMailer module
- Restore of PHPStorm IDE help module
- Improving Test Coverage 

Inspired by
===========
Laravel 5+, CodeIgniter, Yii 2, Symfony 2, Ruby on Rails.
