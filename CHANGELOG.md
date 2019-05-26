# CHANGELOG

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