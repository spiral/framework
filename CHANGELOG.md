# CHANGELOG

## v2.13.0 - Unreleased
- **Medium Impact Changes**
  - Dispatcher `Spiral\Http\SapiDispatcher` is deprecated. Will be moved to `spiral/sapi-bridge` and removed in v3.0
  - Classes `Spiral\Http\Emitter\SapiEmitter`, `Spiral\Http\Exception\EmitterException`, `Spiral\Http\EmitterInterface`,
    `Spiral\Http\SapiRequestFactory` is deprecated. Will be removed in version v3.0.
    After the release of v3.0, must use the package `spiral/sapi-bridge` for SAPI functionality.
  - The `dumper` component is deprecated and will be removed in v3.0
- **Other Features**
  - [spiral/http] Added parameter `chunkSize` in the `http` configuration file.

## v2.12.0 - 2022-04-07
- **Medium Impact Changes**
  - Bootloaders `Spiral\Bootloader\Broadcast\BroadcastBootloader`, `Spiral\Bootloader\Http\WebsocketsBootloader`
    are deprecated. Will be removed in v3.0.
  - Console commands `Spiral\Command\Database\ListCommand`, `Spiral\Command\Database\TableCommand`,
    `Spiral\Command\GRPC\GenerateCommand`, `Spiral\Command\GRPC\ListCommand`, `Spiral\Command\Migrate\AbstractCommand`,
    `Spiral\Command\Migrate\InitCommand`, `Spiral\Command\Migrate\MigrateCommand`, `Spiral\Command\Migrate\ReplayCommand`,
    `Spiral\Command\Migrate\RollbackCommand`, `Spiral\Command\Migrate\StatusCommand` are deprecated. Will be removed in v3.0.
  - Classes `Spiral\Broadcast\Config\WebsocketsConfig`, `Spiral\Broadcast\Middleware\WebsocketsMiddleware`, 
    `Spiral\GRPC\Exception\CompileException`, `Spiral\GRPC\GRPCDispatcher`, `Spiral\GRPC\LocatorInterface`, 
    `Spiral\GRPC\ProtoCompiler`, `Spiral\GRPC\ServiceLocator`, `Spiral\Http\LegacyRrDispatcher`, `Spiral\Http\RrDispatcher`
    are deprecated. Will be removed in v3.0.
  - Changed package replacement strategy. "*" is replaced by "self.version".
  - Sapi emitter now supports streaming emitting.
  - [spiral/data-grid-bridge] Removed deprecation in classes `Spiral\DataGrid\Annotation\DataGrid`, `Spiral\DataGrid\Bootloader\GridBootloader`,
    `Spiral\DataGrid\Config\GridConfig`, `Spiral\DataGrid\Interceptor\GridInterceptor`, `Spiral\DataGrid\Response\GridResponse`,
    `Spiral\DataGrid\Response\GridResponseInterface`, `Spiral\DataGrid\GridInput`.
- **Other Features**
  - [spiral/data-grid-bridge] Added method `addWriter` in `Spiral\DataGrid\Bootloader\GridBootloader`.
  - Extended version of `psr/log` dependency from `^1.0` to `1 - 3`
## v2.11.0 - 2022-03-18
- **High Impact Changes**
  - [spiral/queue] Added queue injector #592
  - [spiral/cache] Added cache injector #600
- **Medium Impact Changes**
  - [spiral/tokenizer] Added ability to use scopes for indexing files with specific scopes #593
- **Other Features**
  - [spiral/boot] Added ability to disable overwriting env variables for `Spiral\Boot\Environment` #599
  - [spiral/storage] Added storage bucket factory #601
  - [spiral/console] Added return types for interface compatibility #591

## v2.10.0 - 2022-03-04
- **High Impact Changes**
- **Medium Impact Changes**
  - [spiral/session] Added `Spiral\Session\SessionFactoryInterface`. Now you can use custom implementation of sessions.
  - [spiral/scaffolder] Console commands `Spiral\Scaffolder\Command\MigrationCommand`, `Spiral\Scaffolder\Command\Database\RepositoryCommand`, 
    `Spiral\Scaffolder\Command\Database\EntityCommand` is deprecated. Will be moved to `spiral/cycle-bridge` and removed in v3.0
  - [spiral/scaffolder] Scaffolder `Spiral\Scaffolder\Declaration\MigrationDeclaration` is deprecated. Will be moved to `spiral/cycle-bridge`
    and removed in v3.0
  - [spiral/attributes] Class annotations will be discovered from class traits.
  - A minimal version of `PHP` increased to `^7.4`
- **Other Features**
  - [spiral/prototype] Added `queue` and `cache` properties
  - [spiral/mailer] Added ability to set delay for messages
  - [spiral/queue] Added NullDriver
  - [spiral/mailer] Class `Spiral\Mailer\Message` is no longer final and is available for extension

## v2.9.1 - 2022-02-11
- **High Impact Changes**
- **Medium Impact Changes**
  - [spiral/sendit] Method `getQueuePipeline` of `Spiral\SendIt\Config\MailerConfig` class is deprecated.
    Use method `getQueue` instead. Added environment variables `MAILER_QUEUE` and `MAILER_QUEUE_CONNECTION`
- **Other Features**
  - Added Symfony 6 support

## v2.9.0 - 2022-02-03
- **High Impact Changes**
- **Medium Impact Changes**
  - Classes `Spiral\Validation\Checker\EntityChecker`, `Spiral\Auth\Cycle\Token`, `Spiral\Auth\Cycle\TokenStorage`, 
    `Spiral\Cycle\RepositoryInjector`, `Spiral\Cycle\SchemaCompiler`, is deprecated. 
    Will be moved to `spiral/cycle-bridge` and removed in v3.0
  - Console commands `Spiral\Command\Cycle\MigrateCommand`, `Spiral\Command\Cycle\SyncCommand`, 
    `Spiral\Command\Cycle\UpdateCommand` is deprecated. Will be moved to `spiral/cycle-bridge` and removed in v3.0
  - Bootloaders `Spiral\Bootloader\Cycle\AnnotatedBootloader`, `Spiral\Bootloader\Cycle\CycleBootloader`,
    `Spiral\Bootloader\Cycle\ProxiesBootloader`, `Spiral\Bootloader\Cycle\SchemaBootloader` is deprecated.
    Use `spiral/cycle-bridge` instead
  - Interceptor `Spiral\Domain\CycleInterceptor` is deprecated. 
    Will be moved to `spiral/cycle-bridge` and removed in v3.0 
  - Scaffolders `Spiral\Scaffolder\Declaration\Database\Entity\AnnotatedDeclaration`, 
    `Spiral\Scaffolder\Declaration\Database\RepositoryDeclaration` is deprecated. Will be moved to `spiral/cycle-bridge` 
    and removed in v3.0 
  - Component `spiral/data-grid-bridge` is deprecated. Will be moved to spiral/cycle-bridge and removed in v3.0
  - Component `spiral/annotations` is deprecated. Use `spiral/attributes` instead
  - A minimal version of `doctrine/annotations` increased to `^1.12`
  - [spiral/validation] Error messages for 'number::lower' and
    'number::higher' rules were changed to reflect that these checks are in
    fact 'lower or equal' and 'higher or equal'. You may need to adjust
    translations file accordingly.
  - [spiral/sendit] Added ability to use `sync` driver for mail queue (#398)
- **Other Features**
  - [spiral/validation] Add array::count, array::range, array::shorter and array::longer
  - [spiral/queue] New component with common interfaces (RR2.0 support) rules (#435)
  - [spiral/cache] New component with common interfaces (RR2.0 support)
  - [spiral/views] [Allow custom loader in ViewManager](https://github.com/spiral/framework/issues/488)
  - [spiral/monolog-bridge] [Added ability to configure Monolog processors](https://github.com/spiral/framework/issues/474)

## v2.8.0 - 2021-06-03

- **New Functionality**
    - Added new `spiral/storage` component (See https://spiral.dev/docs/component-storage)
    - Added new `spiral/distribution` component (See https://spiral.dev/docs/component-distribution)
    - Introduced and improved `spiral/attributes` component (See https://spiral.dev/docs/component-attributes)

- **High Impact Changes**
    - Added `league/flysystem: ^2.0` dependency.

- **Other Features**
    - Added basic RoadRunner 2.0 support (only HTTP)
    - [data-grid] Implement fragment/expression injections for DataGrid sorters (#400)
    - [data-grid] Datagrid/fix inarray accessor edge case (#379)
    - [boot] ExceptionHandler does not account for error_reporting setting (#386)

## v2.7.0 - 2020-12-22

- **High Impact Changes**
    - A minimal version of `monolog/monolog` increased to `^2.2`

- **Other Features**
    - Added PHP 8 support
    - [spiral/prototype] [added typed properties support for php 7.4](https://github.com/spiral/framework/pull/357)
    - [spiral/validation] [arrayOf validation checker](https://github.com/spiral/framework/pull/362)
    - [spiral/validation] introduce `now` parameter in datetime checker
    - [spiral/validation] [extract abstract validator](https://github.com/spiral/framework/issues/358)
    - [spiral/data-grid] [datetime from formatted string value needed](https://github.com/spiral/framework/issues/318)
    - [spiral/data-grid] [add mixed specifications](https://github.com/spiral/framework/issues/320)
    - [spiral/data-grid] [make grid factory more reusable](https://github.com/spiral/framework/issues/319)
    - [spiral/data-grid] [add `ilike` postgres filter](https://github.com/spiral/framework/pull/376)
    - [spiral/domain] [add pipeline interceptor](https://github.com/spiral/framework/pull/370)
    - [spiral/domain] [extract permissions provider for `GuardInterceptor`](https://github.com/spiral/framework/pull/375)

- **Bug Fixes**
    - [spiral/prototype] [name conflict resolver](https://github.com/spiral/framework/issues/326)
    - [spiral/prototype] [trait remove problem](https://github.com/spiral/framework/issues/324)
    - [spiral/prototype] [inherited injections problem](https://github.com/spiral/framework/pull/361)

## v2.6.0 - 2020-09-17

- **High Impact Changes**
    - A minimal version of `symfony/translation` increased to `^5.1`
    - A minimal version of `symfony/console` increased to `^5.1`
    - A minimal version of `symfony/finder` increased to `^5.1`

- **Medium Impact Changes**
    - Dependence of `zend/zend-diactoros` was replaced by `laminas/laminas-diactoros ^2.3`
    - [spiral/dumper] `Spiral\Debug\Dumper` class marked as `final`

- **Other Features**
    - [spiral/http] [Implementation of RFC7231 "Accept" header parser](https://github.com/spiral/framework/issues/231)
    - [spiral/http] [Simplified ErrorHandlerMiddleware injection](https://github.com/spiral/framework/issues/295)
    - [spiral/reactor] [Fix render comment for namespace declaration](https://github.com/spiral/reactor/pull/7)
    - [spiral/validation] [Add `any`, `none` conditions](https://github.com/spiral/validation/pull/13)

## v2.5.0 - 2020-07-18

- RR updated to 1.8.2 (don't use broken 1.8.1 tag)
- Jobs plugin updated to 2.2.0 (see the [changelog](https://github.com/spiral/jobs/releases/tag/v2.2.0))

## v2.4.19 - 2020-06-18

- Allow json requests with empty body by @rauanmayemir

## v2.4.18 - 2020-05-21

- Add the ability to control view cache separately from DEBUG via VIEW_CACHE env variable

## v2.4.17 - 2020-05-09

- added support for Postgres Cycle Auth tokens
- added the ability to redefine the error message in Guarded annotation

## v2.4.16 - 2020-05-05

- Update RR to 1.8.0

## v2.4.15 - 2020-04-21

- [bugfix] invalid value associated with validation context by CycleInterceptor

## v2.4.14 - 2020-04-21

- added `JobRegistry` with the ability to route jobs into custom pipelines
- added the ability to bind job name to the concrete implementation
- added EntityChecker to check the existence and uniqueness of ORM entities
- the Auth/CookieTransport will respect the application base path
- the command `encrypt:key` is no longer private
- cleaned up the Publisher extension by @ncou
- fixed a number of PHPUnit 8.0 warnings
- RouterBootloader no longer blocks the HttpConfig (easier middleware creation)
- show list of actions in multi-action routes in `route:list` by @ncou
- added default alias for `notNull` validation rule
- fixed path behavior in `grpc:generate` command by @matthewhall-ca
- FilterInterceptor can automatically detect the validation context

## v2.4.13 - 2020-03-21

- RoadRunner version updated to 1.7.0

## v2.4.12 - 2020-03-14

- build.sh script updated. Included `-trimpath` and `musl` target
- RoadRunner version updated to 1.6.4

## v2.4.11 - 2020-03-10

- jobs updated to version 2.1.3
- roadrunner updated to version 1.6.3

## v2.4.10 - 2020-03-03

- fix issue with debug http collector fail in console mode

## v2.4.9 - 2020-02-28

- WebSocket module updated to v1.1.0

## v2.4.8 - 2020-02-24

- added reload module

## v2.4.7 - 2020-01-23

- fix `TableCommand` render for fragments
- Add return status code to the ConsoleDispatcher.php

## v2.4.4 - 2019-12-27

- minor code-base quality improvements
- added `route:list` command
- app server updated with recent roadrunner version
- queue server does not show `PUSH` message anymore
- added `roave/security-advisories`

## v2.4.3 - 2019-12-17

- bump `spiral/validation` dependency
- register `DatetimeChecker` in the `ValidationBootloader`

## v2.4.2 - 2019-12-17

- added striker payload validation for jobs
- added support for SerializerRegistryInterface for spiral/jobs

## v2.4.1 - 2019-12-10

- refactor in WebsocketBootloader
- fixed bug in stop sequence of ws roadrunner service

## v2.4.0 - 2019-12-10

- added broadcast service
- added WebSocket server

## v2.3.8 - 2019-11-15

- minor refactoring in json exception handler

## v2.3.7 - 2019-11-14

- bugfix: client exceptions no longer sent with 500 code in application/json payloads
- bugfix: fixed issue with forced expiration of session auth tokens

## v2.3.6 - 2019-11-08

- the framework can work without any snapshooter

## v2.3.5 - 2019-11-08

- improved code coverage
- fixed invalid middleware association for http state collector

## v2.3.4 - 2019-11-08

- improved debug state management
- exceptions are able to display current request state, logs and etc.
- ability to register custom debug state collectors

## v2.3.3 - 2019-11-02

- the plain snapshots enabled by default instead of HTML based
- improved error handling for JSON requests

## v2.3.2 - 2019-11-02

- lighter API and abstract class base injection for `spiral/filters`

## v2.3.1 - 2019-11-02

- FileSnapshotter now sends logs into "default" log channel
- CycleInterceptor use parameter as entity value instead of entity role

## v2.3.0 - 2019-11-01

- added interceptable core support and DomainBootloader
- the ability to automatically inject Cycle entity via route parameter
- the ability to automatically pre-validate Filters
- the ability to authorize controller methods using @Guarded annotation
- improving the Cycle TokenStorage for better testing capabilities

## v2.2.4 - 2019-10-30

- authorizes trait removed in favor of core interceptors
- Auth Cycle\TokenStorage now requests TransactionInterface directly, for simpler testing

## v2.2.3 - 2019-10-30

- json payload middleware read stream content via (string) conversion
- JsonPayloadParserBootloader renamed to JsonPayloadsBootloader

## v2.2.2 - 2019-10-28

- minor CS in CycleBootloader, cleaner dependencies

## v2.2.1 - 2019-10-28

- the ORM schema can be pre-heated automatically
- ability to create injectors to interfaces

## v2.2.0 - 2019-10-24

- added auth component
- new CS fixes in compliance with PSR-12
- minor improvements in session scopes
- added health service to the app server

## v2.1.2 - 2019-10-11

- added CookieManager for simpler access to cookies in request scope
- added SessionScope for simpler access to session in request scope
- added automatic CS formatting

## v2.1.1 - 2019-09-26

- update `cycle/proxy-factory` dependency

## v2.1.0 - 2019-09-23

- `spiral/jobs` updated to `2.0`
- job handlers introduced

## v2.0.19 - 2019-09-16

- typo fix: `JsonPayloadBootload` to `JsonPayloadParserBootloader`

## v2.0.18 - 2019-09-16

- added `JsonPayloadMiddleware` and `JsonPayloadBootloader`
- more tests
- added 7.4 tests to travis
- 7.1 support is officially dropped

## v2.0.17 - 2019-09-07

- ability to inject constructor dependencies into cycle repositories and classes from app container

## v2.0.16 - 2019-09-06

- added support for Prometheus metrics for `jobs` (queue) and `grpc` services
- exceptions will use default style
- bugfix: inability to render `FragmentInterface` in `db:table` command
- `view:compile` won't compile `NativeEngine` templates anymore
- ability to skip server download if version did not change

## v2.0.15 - 2019-07-16

- fixed config method names in spiral/views

## v2.0.14 - 2019-07-16

- cycle/annotated is now based on doctrine/annotations

## v2.0.12 - 2019-07-16

- cycle/annotated is not required to work with cycle anymore

## v2.0.13 - 2019-07-29

- DatabaseTable command has been modified to display composite FKs
- added ability to configure worker relay using ENV RR_WORKER by @myavchik
- automatically configure worker based on rr relay settings

## v2.0.12 - 2019-07-16

- cycle/annotated is not required for cycle to work

## v2.0.11 - 2019-07-01

- added MetricsInterface service for Prometheus
- fixed i18n tests
- updated RoadRunner dependency
- added RR services: headers, metrics

## v2.0.10 - 2019-06-19

- updated dependency (and interfaces) with cycle/proxy-factory

## v2.0.9 - 2019-06-07

- http component split into cookies and csrf packages
- decoupled from zend/diactoros

## v2.0.8 - 2019-06-07

- http component split into cookies and csrf packages
- decoupled from zend/diactoros

## v2.0.8 - 2019-05-29

- added support for pre-loading specific relations using eager or lazy loading methods

## v2.0.7 - 2019-05-27

- cycle:sync does not require configuration now
- added `dumprr` function (dump to STDERR)

## v2.0.6 - 2019-05-26

- no more migration warnings while running static analysis on a project
- fixed container binding for cycle repository
- migration command now adds `\n` after each migration
- bugfix `vendor/bin/spiral get-binary` on linux machines

## v2.0.5 - 2019-05-24

- `grpc:generate` command now includes all proto files from given directory
- `RbacBootloader` renamed to `GuardBootloader`

## v2.0.4 - 2019-05-24

- added `vendor/bin/spiral get-binary` command to download application server by @Alex-Bond

## v2.0.3 - 2019-05-23

- added GRPC dispatcher
- added encrypter:key command

## v2.0.2 - 2019-05-22

- added binary server release
- renamed MvcBootloader to RouterBootloader
- minor cs

## v2.0.0

- TBD

## 1.0.10 - 2018-06-03

- Fixed bug with incorrect Request association for already exists bags
- Added charset UTF-8 for response wrapper
- Improved error handling for CLI applications

## 1.0.7 - 2017-10-04

- Ability to specify JSON response code inside jsonSerialize

## 1.0.6 - 2017-09-28

- Fixed a bug when error message was converted to empty array
- Fixed a bug when multidepth requests contained invalid prefix (in case of depth more than 3)

## 1.0.5

- ability to locate view cache files by view name and namespace

## 1.0.4

- ability to add conditions to skip validation rules

## 1.0.2 - 2017-05-04

- ValidatesEntity now caches last set of produced errors
- Ability to use EntityInterface as parameter for ValidatorInterface->setData() method

## 1.0.1 - 2017-04-20

- Improved cache management for StemplerEngine (fixed issue with non-stable cache key)
- Ability to force view recompilation thought ViewManager

## 1.0.0 - 2017-04-06

- first public release, no BC changes

## 0.9.14 - 2017-03-31

- improvements in Translation indexations

## 0.9.13 - 2017-03-31

- bugfix in Loader caused an exception with Swift::autoloader

## 0.9.12 - 2017-03-24

- `uri` function restored
- Router is now available outside of http scope

## 0.9.11 - 2017-03-22

- Cache directory is now relative to runtime directory

## 0.9.10 - 2017-03-10

- DateTime accessors now can accept DateTimeInterface

## 0.9.6 - 2017-02-07

* Dependencies update
* Validator can now accept checkers outside of it's config

## 0.9.5 - 2017-02-07

* Proper timezone detection for datetime accessors
* RenderException now shows original error location in views
* Improvements in cache management for Twig engine

## 0.9.1 - 2017-02-05

**Encrypter**
* Proper exception when encryption key is invalid

**Session**
* Session does not force session id in cookie when session not started

## 0.9.0 - 2017-02-05

**Framework**
* Dropped support of PHP5+
* Added secure session implementation
* Code coverage improvements
* Twig updated to 2.0 branch
* PHPUnit updated to 5.0 branch
* Components split into separate repositories
* Symfony dependencies updated to 3.0 branch
* added `bind()` function to specify IoC dependencies in configs
* ViewSource class added into views

**Common**
* Dropped support of PHP5+
* Code coverage improvements
* Cache component removed (replaced with PSR-16)
* Views component moved to Framework bundle
* Validation component moved to Framework bundle
* Translation component moved to Framework bundle
* Encryption component moved to Framework bundle
* Migrations component moved in
    * Automatic migration generation is now part of Migration component
* Security component moved in
* Monolog dependency removed
* PHPUnit updated to 5.0 branch
* Symfony dependencies updated to 3.0 branch
* Schema definitions moved to array constants instead of default property values
* Simplified PaginatorInterface
* Reactor component moved in
* Debugger (log manager) component removed
* Improved implementation of Tokenizer component
* Lazy wire declaration for FactoryInterface

**Core**
* ScoperInterface moved into Framework bundle
* Container now validates scalar agument types when supplied by user

**DBAL**
* Improved polyfills for SQLServer
* Improved SQL injection prevention
* Improved timezone management
* Refactoring of DBAL schemas
* Bugfixes
    * Unions with ordering in SQLServer
    * Invalid parameter handling for update queries with nested selection
* Pagination classes are immutable now
* Ability to use nested queries in JOIN statements
* on argument in join() methods is deprecated, use on() function directly

**Models**
* Removed features
    * Embedded validations
    * Magic getter and setter methods via __call()
* setValue and packValue methods added
* "fields" property is now private
* SolidableTrait is now part of models
  **ORM**
* Refactoring of SchemaBuilder
* RecordSelector does not extend SelectQuery anymore
* Transactional (UnitOfWork) support
* Improvements in memory mapping
* Improvements in tree operations (save)
* Removed features
    * ActiveRecord thought direct table communication
    * MutableNumber accessor
    * Validations
* Bugfixes
    * ManyToMany relations to non-saved records
    * BelongsToRelation to non-saved records
* Definition of morphed relations must be explicit now
* All ORM entities MUST have proper primary key now
* Ability to define custom column types in combination with accessors
* Relation loaders and schemas are immutable now
* Memory scope are optional now
* Record does not have "source" method by default now (see SourceTrait)
* Optimizations in load method with "using" option
* Added late bindings (locate outer record based on Interface or role name)
* Added detach() method into HasMany relation

**Stempler**
* StemplerLoader synced with Twig abstraction, StemplerSource introduced
* SourceContext (twig like) have been added

**ODM**
* Moved to latest PHP7 mongo drivers
* Removed features
    * Validations
* Removed parent document reference in compositions
* Scalar array split into multiple versions
* CompositableInterface improved
* Document does not have "source" method by default now (see SourceTrait)

**Storage**
* Improved implementation of RackspaceServer
* Added GridFS server support
