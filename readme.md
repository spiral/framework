Spiral modular RAD Framework (beta)
=======================
[![Latest Stable Version](https://poser.pugx.org/spiral/framework/v/stable)](https://packagist.org/packages/spiral/framework) [![Total Downloads](https://poser.pugx.org/spiral/framework/downloads)](https://packagist.org/packages/spiral/framework) [![License](https://poser.pugx.org/spiral/framework/license)](https://packagist.org/packages/spiral/framework) [![Build Status](https://travis-ci.org/spiral/spiral.svg?branch=master)](https://travis-ci.org/spiral/spiral) [![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/spiral/hotline)

The Spiral framework provides a modular Rapid Application Development (RAD) platform to develop web applications using an HMVC architecture, modern practices, PSR-7, simple syntax and customizable scaffolding mechanisms.

[**Skeleton App**](https://github.com/spiral-php/application) | [Guide](https://github.com/spiral-php/guide) | [Gitter](https://gitter.im/spiral/hotline) | [**Forum**](https://groups.google.com/forum/#!forum/spiral-framework) | [Twitter](https://twitter.com/spiralphp) | [**Foundation Components**](https://github.com/spiral/components)

Spiral framework has not been publicly released/announced yet due guide is still in progress. :/

Temporary in transition
=======================
* PHPStorm IDE help module (ORM and ODM)
* Guide update (current version requires some updates as new coding alternatives were created)
* Security module

Examples:
========

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
     * @param Database   $logDatabase
     * @param HttpConfig $config
     * @return string
     */
    public function indexAction(Database $database, Database $logDatabase, HttpConfig $config)
    {
        dump($config->basePath());
    
        $logDatabase->table('log')->insert(['message' => 'Yo!']);
    
        return $this->views->render('welcome', [
            'users' => $database->table('users')->select()->where('name', 'John')->all()
        ]);
    }
}
```
```php
class Post extends Record 
{
    use TimestampsTrait;

    //Database partitions, isolation and aliasing
    protected $database = 'blog';

    protected $schema = [
        'id'     => 'bigPrimary',
        'title'  => 'string(64)',
        'status' => 'enum(published,draft)',
        'body'   => 'text',
        
        //Simple relation definition (optional)
        'author'   => [self::BELONGS_TO => Author::class],
        'comments' => [self::HAS_MANY => Comment::class]
        
        //Not too simple relation definitions (optional)
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
//Post::find() == $this->orm->selector(Post::class) == PostSource->find()
$posts = Post::find()
    ->distinct()
    ->with('comments') //Automatic joins
    ->with('author')->where('author.name', 'LIKE', $authorName) //Fluent
    ->load('comments.author') //Cascade eager-loading
    ->paginate(10) //Quick pagination using active request
    ->all();

foreach($posts as $post) {
    echo $post->author->getName();
}
```

```html
<spiral:grid source="<?= $uploads ?>" as="upload">
    <grid:cell title="ID:" value="<?= $upload->getId() ?>"/>
    <grid:cell title="Time Created:" value="<?= $upload->getTimeCreated() ?>"/>
    <grid:cell title="Label:" value="<?= e($upload->getLabel()) ?>"/>

    <!-- Behaves as usual html tag -->
    <grid:cell.bytes title="Filesize:" value="<?= $upload->getFilesize() ?>" style="color: blue;"/>

    <grid:cell>
        <a href="<?= uri('uploads::edit', $upload) ?>">Edit</a>
    </grid:cell>
</spiral:grid>

<spiral:cache lifetime="10">
    <?= mt_rand(0, 100) ?>
</spiral:cache>
```
