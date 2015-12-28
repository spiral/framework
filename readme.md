Spiral RAD PSR-7 HMVC Framework (beta)
=======================
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/v/stable)](https://packagist.org/packages/spiral/framework) [![Total Downloads](https://poser.pugx.org/spiral/framework/downloads)](https://packagist.org/packages/spiral/framework) [![License](https://poser.pugx.org/spiral/framework/license)](https://packagist.org/packages/spiral/framework) [![Build Status](https://travis-ci.org/spiral/spiral.svg?branch=master)](https://travis-ci.org/spiral/spiral) [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/spiral/hotline)

The Spiral framework provides a modular Rapid Application Development (RAD) platform to develop web applications using an HMVC architecture, modern practices, PSR-7, simple syntax and powerful scaffolding mechanisms (temporary in transition).

[**Skeleton App**](https://github.com/spiral-php/application) | [Website](prod-url-here) | [Guide](https://github.com/spiral/guide) | [**Contribute**](https://github.com/spiral/guide/blob/master/contributing.md) | [Gitter](https://gitter.im/spiral/hotline) | [**Components**](https://github.com/spiral/components)

Temporary in transition
=======================
* Scaffolding module (scaffolding engine has already been built)
* PHPStorm IDE help module (ORM and ODM)
* Documentation update (current version requires some updates as new coding alternatives were created)

Examples:
========

```php
class HomeController extends Controller
{
    /**
     * Spiral can automatically deside what database/cache/storage
     * instance to provide for every action parameter.
     *
     * @param Database $database
     * @param Database $logDatabase
     * @return string
     */
    public function indexAction(Database $database, Database $logDatabase)
    {
        $logDatabase->table('log')->insert(['message' => 'Yo!']);
    
        return $this->views->render('welcome', [
            'users' => $database->table('users')->select()->all()
        ]);
    }
}
```

PSR-7 integration and method injections:

```php
public function indexAction(ResponseInterface $response)
{
    return $response->withHeader('Spiral', 'Value!');
}
```

JSON responses

```php
public function indexAction(ServerRequestInterface $request)
{
    return [
        'status' => 200,
        'uri'    => (string)$request->getUri()
    ];
}
```

Contextual injections and database introspection:

![Databases](https://raw.githubusercontent.com/spiral/guide/master/resources/db-schema.gif)

StorageManger to simplify process of working with remote storages:

```php
public function uploadAction(StorageBucket $uploads)
{
    $object = $bucket->put(
        'my-upload.file',
        $this->input->files->get('upload')
    );
    
    //...
    
    echo $object->replace('amazon')->getAddress();
}
```

Powerful ORM with automatic scaffolding for MySQL, PostgresSQL, SQLite, SQLServer

```php
class Post extends Record 
{
    use TimestampsTrait;

    //Database partitions and isolation
    protected $database = 'blog';

    protected $schema = [
        'id'     => 'bigPrimary',
        'title'  => 'string(64)',
        'status' => 'enum(published,draft)',
        'body'   => 'text',
        
        //Simple relation definition
        'author'   => [self::BELONGS_TO => Author::class],
        'comments' => [self::HAS_MANY => Comment::class]
    ];
}
```

```php
$posts = Post::find()
    ->with('comments') //Automatic joins
    ->with('author')->where('author.name', 'LIKE', $authorName) //Fluent
    ->load('comments.author') //Cascade eager-loading
    ->paginate(10) //Quick and easy pagination
    ->all();

foreach($posts as $post) {
    echo $post->author->getName();
}
```

```php
public function indexAction(ClassLocatorInterface $locator, InvocationLocatorInterface $invocations)
{
    //Embedded functionality for static analysis of your code
    dump($locator->getClasses(ControllerInterface::class));
}
```

Shared components and shortcuts to container bindings:

![Shared bindings](https://raw.githubusercontent.com/spiral/guide/master/resources/virtual-bindings.gif)

Extendable and programmable HTML Stempler compatible with any command syntax:

```html
<spiral:grid source="<?= $uploads ?>" as="upload">
    <grid:cell title="ID:" value="<?= $upload->getId() ?>"/>
    <grid:cell title="Time Created:" value="<?= $upload->getTimeCreated() ?>"/>
    <grid:cell.bytes title="Filesize:" value="<?= $upload->getFilesize() ?>"/>

    <grid:cell>
        <a href="<?= uri('uploads::edit', $upload) ?>">Edit</a>
    </grid:cell>
</spiral:grid>

<spiral:cache lifetime="10">
    <?= mt_rand(0, 100) ?>
</spiral:cache>
```
> You can write your own virtual tags (similar to web components) with almost any functionality or connect external libraries.

Frontend toolkit with AJAX forms and widgets:

```html
<spiral:form action="/sample/save/<?= $entity ?>">
    <form.input label="Value" name="value" value="<?= $entity->child->value ?>"/>
    <input type="submit" class="btn btn-default" value="[[Update Element]]"/>
</spiral:form>
```

![Form](https://raw.githubusercontent.com/spiral/guide/master/resources/form.gif)

Also included
=============

Plug and Play extensions, IDE friendly, frontend toolkit, static analysis, cache and logic cache, 
meta programing ideas, cloud storages, scaffolding, auto-indexed translator, Interop Container,
Zend Diactoros, Symfony Console, Symfony Translation, Symfony Events, Monolog, Twig, 
debugging/profiling tools and much more!

Inspired by
===========
Internal CMS/CMF, Laravel, Yii 2, Symfony 2, Lithium, Ruby on Rails (conceptually), CodeIgniter.
