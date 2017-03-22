CHANGELOG for 0.9.0 RC
======================

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
