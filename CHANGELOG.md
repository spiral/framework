# CHANGELOG

v2.3.4 (08.11.2019) - no binary
-----
- improved debug state management
- exceptions are able to display current request state, logs and etc.
- ability to register custom debug state collectors

v2.3.3 (02.11.2019) - no binary
-----
- the plain snapshots enabled by default instead of HTML based
- improved error handling for JSON requests

v2.3.2 (02.11.2019) - no binary
-----
- lighter API and abstract class base injection for `spiral/filters`

v2.3.1 (02.11.2019) - no binary
-----
- FileSnapshotter now sends logs into "default" log channel
- CycleInterceptor use parameter as entity value instead of entity role

v2.3.0 (01.11.2019) - no binary
-----
- added interceptable core support and DomainBootloader
- the ability to automatically inject Cycle entity via route parameter
- the ability to automatically pre-validate Filters
- the ability to authorize controller methods using @Guarded annotation
- improving the Cycle TokenStorage for better testing capabilities

v2.2.4 (30.10.2019) - no binary
-----
- authorizes trait removed in favor of core interceptors
- Auth Cycle\TokenStorage now requests TransactionInterface directly, for simpler testing

v2.2.3 (30.10.2019) - no binary
-----
- json payload middleware read stream content via (string) conversion
- JsonPayloadParserBootloader renamed to JsonPayloadsBootloader

v2.2.2 (28.10.2019) - no binary
-----
- minor CS in CycleBootloader, cleaner dependencies

v2.2.1 (28.10.2019) - no binary
-----
- the ORM schema can be pre-heated automatically
- ability to create injectors to interfaces 

v2.2.0 (24.10.2019)
-----
- added auth component
- new CS fixes in compliance with PSR-12
- minor improvements in session scopes
- added health service to the app server

v2.1.2 (11.10.2019) - no binary
------
- added CookieManager for simpler access to cookies in request scope
- added SessionScope for simpler access to session in request scope
- added automatic CS formatting

v2.1.1 (26.09.2019) - no binary
------
- update `cycle/proxy-factory` dependency

v2.1.0 (23.09.2019)
------
- `spiral/jobs` updated to `2.0`
- job handlers introduced

v2.0.19 (16.09.2019) - no binary
------
- typo fix: `JsonPayloadBootload` to `JsonPayloadParserBootloader` 

v2.0.18 (16.09.2019) - no binary
------
- added `JsonPayloadMiddleware` and `JsonPayloadBootloader`
- more tests
- added 7.4 tests to travis
- 7.1 support is officially dropped

v2.0.17 (07.09.2019) - no binary
------
- ability to inject constructor dependencies into cycle repositories and classes from app container

v2.0.16 (06.09.2019)
------
- added support for Prometheus metrics for `jobs` (queue) and `grpc` services
- exceptions will use default style
- bugfix: inability to render `FragmentInterface` in `db:table` command
- `view:compile` won't compile `NativeEngine` templates anymore
- ability to skip server download if version did not change

v2.0.15 (16.07.2019) - no binary
------
- fixed config method names in spiral/views

v2.0.14 (16.07.2019)
------
- cycle/annotated is now based on doctrine/annotations

v2.0.12 (16.07.2019)
------
- cycle/annotated is not required to work with cycle anymore

v2.0.13 (29.07.2019)
------
- DatabaseTable command has been modified to display composite FKs
- added ability to configure worker relay using ENV RR_WORKER by @myavchik 
- automatically configure worker based on rr relay settings

v2.0.12 (16.07.2019)
------
- cycle/annotated is not required for cycle to work

v2.0.11 (01.07.2019)
------
- added MetricsInterface service for Prometheus
- fixed i18n tests
- updated RoadRunner dependency
- added RR services: headers, metrics

v2.0.10 (19.06.2019)
------
- updated dependency (and interfaces) with cycle/proxy-factory

v2.0.9 (07.06.2019)
------
- http component split into cookies and csrf packages
- decoupled from zend/diactoros

v2.0.8 (07.06.2019)
------
- http component split into cookies and csrf packages
- decoupled from zend/diactoros

v2.0.8 (29.05.2019)
------
- added support for pre-loading specific relations using eager or lazy loading methods

v2.0.7 (27.05.2019)
------
- cycle:sync does not require configuration now
- added `dumprr` function (dump to STDERR)

v2.0.6 (26.05.2019)
------
- no more migration warnings while running static analysis on a project
- fixed container binding for cycle repository
- migration command now adds `\n` after each migration
- bugfix `vendor/bin/spiral get-binary` on linux machines

v2.0.5 (24.05.2019)
------
- `grpc:generate` command now includes all proto files from given directory
- `RbacBootloader` renamed to `GuardBootloader`

v2.0.4 (24.05.2019)
------
- added `vendor/bin/spiral get-binary` command to download application server by @Alex-Bond

v2.0.3 (23.05.2019)
------
- added GRPC dispatcher
- added encrypter:key command

v2.0.2 (22.05.2019)
------
- added binary server release
- renamed MvcBootloader to RouterBootloader
- minor cs

v2.0.0
------
- TBD

1.0.10 (03.06.2018)	
-----	
- Fixed bug with incorrect Request association for already exists bags	
- Added charset UTF-8 for response wrapper	
- Improved error handling for CLI applications	

 1.0.7 (04.10.2017)	
-----	
- Ability to specify JSON response code inside jsonSerialize	

 1.0.6 (28.09.2017)	
-----	
- Fixed a bug when error message was converted to empty array	
- Fixed a bug when multidepth requests contained invalid prefix (in case of depth more than 3)	

 1.0.5	
-----	
- ability to locate view cache files by view name and namespace	

 1.0.4	
-----	
- ability to add conditions to skip validation rules	

 1.0.2 (04.05.2017)	
-----	
- ValidatesEntity now caches last set of produced errors	
- Ability to use EntityInterface as parameter for ValidatorInterface->setData() method	

 1.0.1 (20.04.2017)	
-----	
- Improved cache management for StemplerEngine (fixed issue with non-stable cache key)	
- Ability to force view recompilation thought ViewManager	

 1.0.0 (06.04.2017)	
-----	
- first public release, no BC changes	

 0.9.14 (31.03.2017)	
-----	
- improvements in Translation indexations	

 0.9.13 (31.03.2017)	
-----	
- bugfix in Loader caused an exception with Swift::autoloader	

 0.9.12 (24.03.2017)	
-----	
- `uri` function restored	
- Router is now available outside of http scope	

 0.9.11 (22.03.2017)	
-----	
- Cache directory is now relative to runtime directory	

 0.9.10 (10.03.2017)	
-----	
- DateTime accessors now can accept DateTimeInterface	

 0.9.6 (07.02.2017)	
-----	
* Dependencies update	
* Validator can now accept checkers outside of it's config	

 0.9.5 (07.02.2017)	
-----	
* Proper timezone detection for datetime accessors	
* RenderException now shows original error location in views	
* Improvements in cache management for Twig engine	

 0.9.1 (05.02.2017)	
-----	
**Encrypter**	
  * Proper exception when encryption key is invalid	

 **Session**	
  * Session does not force session id in cookie when session not started	

 0.9.0 (05.02.2017)	
-----	
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
