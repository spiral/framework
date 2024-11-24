# CHANGELOG

## 3.14.6 - 2024-10-22

- **Bug Fixes**
    - [spiral/core] `ServerRequestInterface` is always resolved into a Proxy in the `http` scope
    - [spiral/cache] `EventDispatcher` is now injected into `CacheManager`

## 3.14.4 - 2024-09-23

- **Bug Fixes**
  - [spiral/router] Router now uses proxied container to create middlewares in a right scope.
  - [spiral/router] Better binding for the interceptor handler.
  - `DebugBootloader` now uses a Factory Proxy to resolve collectors.
    Unresolved collectors don't break state populating flow.

## 3.14.3 - 2024-09-11

- **Bug Fixes**
  - [spiral/core] Improved introspecting of Container when a Container Proxy is provided into the `Introspector`.
  - [spiral/http] Improved exception message when Input Manager can't get a Request in because  of wrong scope.
  - `GuardScope` has been deprecated. Use `GuardInterface` directly instead.

## 3.14.2 - 2024-09-10

- **Bug Fixes**
  - [spiral/core] Added a proxy recursion detection a dependency on resolving: a `RecursiveProxyException` will be
    thrown in this case.
  - [spiral/boot] Fixed concurrent writing and reading cached data on workers boot.
- Increased code quality by Rector.

## 3.14.1 - 2024-09-09

- **Bug Fixes**
  - [spiral/router] Fixed fallback interceptors handler in `AbstractTarget`.
- Increased code quality by Rector.

## 3.14.0 - 2024-09-03

- **High Impact Changes**
  - Scopes
    - Deeper integration with Container Scopes: contextual scopes are created inside dispatcher scopes.
  - Interceptors
    - Added a new package `spiral/interceptors`.
    - `spira/hmvc` is deprecated now.

## 3.13.0 - 2024-05-22

- **Other Features**
  - [spiral/queue] Added `Spiral\Queue\TaskInterface` and `Spiral\Queue\Task` which will contain all the necessary data
    for job processing.

## 3.12.0 - 2024-02-29

- **Medium Impact Changes**
  - [spiral/core] Interface `Spiral\Core\Container\SingletonInterface` is deprecated,
    use `Spiral\Core\Attribute\Singleton` instead. Will be removed in v4.0.
- **Other Features**
  - Added `Spiral\Scaffolder\Command\InfoCommand` console command for getting information about available scaffolder 
    commands.
  - [spiral/core] Added the ability to bind the interface as a proxy via `Spiral\Core\Config\Proxy` or `Spiral\Core\Config\DeprecationProxy`.
  - [spiral/core] Added the ability to configure the container using `Spiral\Core\Options`. Added option **checkScope** 
    to enable scope checking.

## 3.11.1 - 2023-12-29

- **Bug Fixes**
  - [spiral/tokenizer] Fixed finalize for listeners
- **Other Features**
    - Added Tokenizer Listeners to the `Spiral\Command\Tokenizer\InfoCommand` console command.
    - Added `Spiral\Command\Tokenizer\ValidateCommand` console command for validating Tokenizer listeners.

## 3.11.0 - 2023-12-21

- **Other Features**
    - The `Spiral\Debug\Config\DebugConfig` has been added for easy addition of **tags** and **collectors**.
    - [spiral/console] The ability to use **enum** as an option in a console command when configuring it with attributes has been added.

## 3.10.0 - 2023-11-24

- **Other Features**
    - [spiral/boot] Added `Spiral\Boot\Bootloader\BootloaderRegistryInterface` and `Spiral\Boot\Bootloader\BootloaderRegistry`
      to allow for easier management of bootloaders.

## 3.9.0 - 2023-10-19

- **Other Features**
    - [spiral/queue] Added `Spiral\Queue\Interceptor\Consume\RetryPolicyInterceptor` to enable automatic job retries 
      with a configurable retry policy.
    - [spiral/monolog-bridge] Added the ability to configure the **Monolog** messages format via environment variable 
      `MONOLOG_FORMAT`.
    - [spiral/translator] Added the ability to register additional locales directories.
    - [spiral/prototype] Added console command `Spiral\Prototype\Command\ListCommand` for listing prototype dependencies.

## 3.8.4 - 2023-09-08

- **Bug Fixes**
    - [spiral/storage] Fixed `visibility` in the Storage configuration
    - [spiral/tokenizer] Improved `Tokenizer Info` console command
    - [spiral/debug] Assigning `null` instead of using `unset` in the reset method
    - [spiral/core] Added checking `hasInstance` in the parent scope

## 3.8.3 - 2023-08-29

- **Bug Fixes**
    - [spiral/core] Fixed with checking singletons in the `hasInstance` method

## 3.8.2 - 2023-08-16

- **Bug Fixes**
    - [spiral/core] Adding `force` parameter to the `bindSingleton` method

## 3.8.1 - 2023-08-16

- **Bug Fixes**
    - [spiral/events] Fixed Event Dispatcher rebinding
    - [spiral/router] Fixed incorrect Concatenation of Route Pattern with Prefix in Route Group
    - [spiral/boot] Fixed loading ENV variables from dotenv in Kernel System section
- **Other Features**
    - [spiral/attributes] Added the ability to configure the Attributes cache or disable the cache

## 3.8.0 - 2023-08-14

- **Medium Impact Changes**
    - [spiral/core] Migration a significant portion of the internal operations from runtime to configuration time.
    - [spiral/core] Replaced the previous array-based structure that was utilized to store information about bindings
      within the container. The new approach involves the utilization of Data Transfer Objects (DTOs).
    - [spiral/core] Added a new container scope interface Spiral\Core\ContainerScopeInterface that can be used to run
      code withing isolated IoC scope.
    - [spiral/scaffolder] Method `baseDirectory` of `Spiral\Scaffolder\Config\ScaffolderConfig` class is deprecated.
- **Other Features**
    - [spiral/tokenizer] Added the ability to look for interfaces and enums.
    - [spiral/tokenizer] Added `tokenizer:info` console command
    - [spiral/prototype] Added PHP 8.1 support for prototype injector
    - [spiral/auth] Added `Spiral\Auth\TokenStorageScope`, this class can be used to get the concrete implementation of
      the token storage in a current container scope.
    - [spiral/auth-http] Added a `Spiral\Auth\TokenStorageInterface` binding in
      the `Spiral\Auth\Middleware\AuthMiddleware`
      with the used TokenStorage.
    - [spiral/filters] Added `Spiral\Filters\Model\Mapper\Mapper` that sets values for filter properties. It utilizes a collection of casters, each designed to handle a specific type of value.
    - [spiral/filters] 
    - [spiral/scaffolder] Added new public method `declarationDirectory` to
      the `Spiral\Scaffolder\Config\ScaffolderConfig`
      class that returns the directory path of the specified declaration, or default directory path if not specified.
    - [spiral/attributes] Added the ability to disable annotations reader support and the ability to replace
      instantiator for attributes reader
    - Added support `psr/http-message` v2
    - Added PHPUnit 10 support
- **Bug Fixes**
    - [spiral/paginator] Fixed problem when paginator doesn't calculate `countPages` correctly in constructor
    - [spiral/router] Fixed issue with default parameter values
    - [spiral/auth-http] Setting default transport in `AuthTransportMiddleware`
    - [spiral/filters] Fixed nullable Nested Filters

## 3.7.1 - 2023-04-21

- **Bug Fixes**
    - [spiral/filters] Fixed InputScope to allow retrieval of non-bag input sources
    - [spiral/pagination] Fixed problem when paginator doesn't calculate `countPages` correctly in constructor

## 3.7.0 - 2023-04-13

- **Medium Impact Changes**
    - [spiral/queue] Added the ability to use mixed types as job payload.
- **Bug Fixes**
    - [spiral/scaffolder] Fixed the problem with redefined command types.
    - [spiral/console] Fixed the problem with commands description with signature definition.
    - [spiral/tokenizer] Fixed the problem with using named parameters in class located by a tokenizer.
    - [spiral/telemetry] Fixed LogTracer elapsed time log.
- **Other Features**
    - [spiral/console] Added the ability to guess **option mode**, unless it is explicitly passed in the
      `Spiral\Console\Attribute\Option` attribute.
    - Updated psalm version to 5.0.
    - Added support doctrine/annotations 2.x

## 3.6.1 - 2023-02-20

- **Bug Fixes**
    - [spiral/scaffolder] Fixed the problem with namespace option in some scaffolder commands.

## 3.6.0 - 2023-02-17

- **High Impact Changes**
    - [spiral/tokenizer] Added the ability to cache tokenizer listeners.
    - [spiral/core] Container with isolated memory scopes.
- **Medium Impact Changes**
    - A minimal version of `symfony/console` increased to `^6.1`.
- **Other Features**
    - [spiral/core] Added container `Singleton` attribute to replace `Spiral\Core\SingletonInterface`.
    - [spiral/console] Added the ability to configure console commands via attributes.
    - [spiral/console] Added the ability to prompt for missing required arguments.
    - [spiral/scaffolder] Added the ability to specify a custom `namespace` in the
      `Spiral\Scaffolder\Command\BootloaderCommand`, `Spiral\Scaffolder\Command\CommandCommand`,
      `Spiral\Scaffolder\Command\ConfigCommand`, `Spiral\Scaffolder\Command\ControllerCommand`,
      `Spiral\Scaffolder\Command\JobHandlerCommand`, `Spiral\Scaffolder\Command\MiddlewareCommand` console commands.
    - [spiral/cache] Added the ability to configure the prefix in the storage alias.
    - Added `defineInterceptors` method in `Spiral\Bootloader\DomainBootloader` class.
    - [spiral/filter] Makes Setter attribute for the spiral/filters component repeatable.
    - [spiral/sendit] Adds custom transports registrar for SendIt component.
- **Bug Fixes**
    - [spiral/filters] Fixed problem with validation nested filters.
    - [spiral/core] Fixed infinite recursion on using for class name binding to the same class name.
    - [spiral/queue] Removing the `QueueInterface` binding as a singleton.
    - [spiral/core] Fixed the problem with singleton objects creation with custom arguments.

## 3.5.0 - 2022-12-23

- **Medium Impact Changes**
    - [spiral/reactor] Method `removeClass` of `Spiral\Reactor\Partial\PhpNamespace` class is deprecated. Use
      method `removeElement` instead.
    - [spiral/boot] Deprecated Kernel constants and add new function `defineSystemBootloaders` to allow for more
      flexibility in defining system bootloaders.
- **Other Features**
    - [spiral/router] Added named route patterns registry `Spiral\Router\Registry\RoutePatternRegistryInterface` to
      allow for easier management of route patterns.
    - [spiral/exceptions] Improved the exception trace output for both the plain and console renderers to provide more
      detailed information about previous exceptions.
    - [spiral/exceptions] Made the Verbosity enum injectable to allow for easier customization and management of
      verbosity levels from env variable `VERBOSITY_LEVEL`.
    - [spiral/reactor] Added methods `removeElement`, `getClass`, `getElements`, `getEnum`, `getEnums`, `getTrait`,
      `getTraits`, `getInterface`, `getInterfaces` in the class `Spiral\Reactor\Partial\PhpNamespace`.
    - [spiral/reactor] Added methods `getElements`, `getEnum`, `getEnums`, `getTrait`,
      `getTraits`, `getInterface`, `getInterfaces` in the class `Spiral\Reactor\FileDeclaration`.

## 3.4.0 - 2022-12-08

- **Medium Impact Changes**
    - [spiral/boot] Class `Spiral\Boot\BootloadManager\BootloadManager` is deprecated. Will be removed in version v4.0.
    - [spiral/stempler] Adds null locale processor to remove brackets `[[ ... ]]` when don't use Translator component.
- **Other Features**
    - [spiral/session] Added session handle with cache driver.
    - [spiral/router] Added routes with `PATCH` method into `route:list` command.
    - [spiral/boot] Added `Spiral\Boot\BootloadManager\InitializerInterface`. This will allow changing the
      implementation
      of this interface by the developer.
    - [spiral/boot] Added `Spiral\Boot\BootloadManager\StrategyBasedBootloadManager`. It allows the implementation of a
      custom bootloaders loading strategy.
    - [spiral/boot] Added the ability to register application bootloaders via object instance or anonymous object.
    - [spiral/boot] Removed `final` from the `Spiral\Boot\BootloadManager\Initializer` class.
- **Bug Fixes**
    - [spiral/views] Fixed problem with using view context with default value.
    - [spiral/queue] Added `Spiral\Telemetry\Bootloader\TelemetryBootloader` dependency to QueueBootloader.
    - [spiral/core] (PHP 8.2 support) Fixed problem with dynamic properties in `Spiral\Core\Container`.

## 3.3.0 - 2022-11-17

- **High Impact Changes**
    - [spiral/router] Added the ability to add a `prefix` to the `name` of all routes in a **group**.
    - [spiral/auth] Added `Spiral\Auth\TokenStorageProviderInterface` to allow custom token storages and an ability to
      set default token storage via `auth` config.
    - [spiral/telemetry] Added new component to collect and report application metrics.
- **Medium Impact Changes**
    - Removed go files from the repository
- **Other Features**
    - [spiral/auth-http] Added `Spiral\Auth\Middleware\Firewall\RedirectFirewall` middleware to redirect user to login
      page if they are not authenticated.
- **Bug Fixes**
    - [spiral/http] Fixed error suppressing in the `Spiral\Http\Middleware\ErrorHandlerMiddleware`
    - [spiral/stempler] Fixed documentation link
    - [spiral/auth] Fixed downloads badge

## 3.2.0 - 2022-10-21

- **High Impact Changes**
- **Medium Impact Changes**
- **Other Features**
    - [spiral/queue] Added the ability to pass headers in the `headers` parameter in the job handlers.
    - [spiral/telemetry] Added new component
    - [spiral/queue] Added new option `headers` in the `Spiral\Queue\Options` and new
      interface `Spiral\Queue\ExtendedOptionsInterface`.
    - [spiral/events] Added event interceptors.
    - [spiral/core] Added container instance to callback function parameters in `Spiral\Core\Container` and
      `Spiral\Core\ContainerScope`.
    - [spiral/core] Improved ContainerException message
- **Bug Fixes**
    - [spiral/queue] Fixed problem with using push interceptors in Queue component

## v3.1.0 - 2022-09-29

- **Other Features**
    - [spiral/filters] Added `Spiral\Filter\ValidationHandlerMiddleware` for handling filter validation exception.
    - [spiral/router] Fixed the problem with parsing a pattern with `0` value in route parameter.
    - [spiral/validation] Added the ability to configure the default validator via method `setDefaultValidator`
      in the `Spiral\Validation\Bootloader\ValidationBootloader`.

## v3.0.2 - 2022-09-29

- **Bug Fixes**
    - Removed readonly from `Spiral\Stempler\Transform\Import\Bundle`
    - Fixed the problem with parsing a route pattern with zero value #773
    - Fixed phpdoc for AuthorizationStatus::$topics property

## v3.0.0 - 2022-09-13

- **High Impact Changes**
    - Component `spiral/data-grid-bridge` is removed from `spiral/framework` repository.
      Please, use standalone package `spiral/data-grid-bridge` instead.
    - Component `spiral/data-grid` is removed from `spiral/framework` repository.
      Please, use standalone package `spiral/data-grid` instead.
    - `Spiral\Boot\ExceptionHandler` has been eliminated. New `Spiral\Exceptions\ExceptionHandler` with interfaces
      `Spiral\Exceptions\ExceptionHandlerInterface`, `Spiral\Exceptions\ExceptionRendererInterface` and
      `Spiral\Exceptions\ExceptionReporterInterface` have been added.
    - Console commands `Spiral\Command\Cycle\MigrateCommand`, `Spiral\Command\Cycle\SyncCommand`,
      `Spiral\Command\Cycle\UpdateCommand`, `Spiral\Scaffolder\Command\MigrationCommand`,
      `Spiral\Scaffolder\Command\Database\EntityCommand`, `Spiral\Scaffolder\Command\Database\RepositoryCommand`,
      `Spiral\Command\Database\ListCommand`, `Spiral\Command\Database\TableCommand`,
      `Spiral\Command\Migrate\InitCommand`, `Spiral\Command\Migrate\MigrateCommand`,
      `Spiral\Command\Migrate\ReplayCommand`, `Spiral\Command\Migrate\RollbackCommand`,
      `Spiral\Command\Migrate\StatusCommand` is removed.
      Use same console commands from `spiral/cycle-bridge` package.
    - Console commands `Spiral\Command\GRPC\ListCommand`, `Spiral\Command\GRPC\GenerateCommand` is removed.
      Use same console commands from `spiral/roadrunner-bridge` package.
    - Classes `Spiral\Auth\Cycle\Token`, `Spiral\Auth\Cycle\TokenStorage`, `Spiral\Cycle\RepositoryInjector`,
      `Spiral\Cycle\SchemaCompiler`, `Spiral\Domain\CycleInterceptor` is removed.
      Use same classes from `spiral/cycle-bridge` instead.
    - Bootloaders `Spiral\Bootloader\Jobs\JobsBootloader`, `Spiral\Bootloader\Server\LegacyRoadRunnerBootloader`,
      `Spiral\Bootloader\Server\RoadRunnerBootloader`, `Spiral\Bootloader\ServerBootloader`,
      `Spiral\Bootloader\GRPC\GRPCBootloader` is removed.
      Use `spiral/roadrunner-bridge` package.
    - Bootloaders `Spiral\Bootloader\Cycle\AnnotatedBootloader`, `Spiral\Bootloader\Cycle\CycleBootloader`,
      `Spiral\Bootloader\Cycle\ProxiesBootloader`, `Spiral\Bootloader\Cycle\SchemaBootloader`,
      `Spiral\Bootloader\Database\DatabaseBootloader`, `Spiral\Bootloader\Database\DisconnectsBootloader`,
      `Spiral\Bootloader\Database\MigrationsBootloader` is removed.
      Use `spiral/cycle-bridge` package.
    - Bootloader `Spiral\Bootloader\Broadcast\BroadcastBootloader` is removed. Use `spiral/roadrunner-broadcast` package
      instead.
    - Bootloader `Spiral\Bootloader\Http\WebsocketsBootloader` is removed.
    - Component `spiral/annotations` is removed. Use `spiral/attributes` instead.
    - Added return type `void` to a methods `publish`, `publishDirectory`, `ensureDirectory`
      in `Spiral\Module\PublisherInterface` interface.
    - Removed `Spiral\Http\SapiDispatcher` and `Spiral\Http\Emitter\SapiEmitter`. Please, use
      package `spiral/sapi-bridge` instead.
    - Bootloader `Spiral\Bootloader\Http\DiactorosBootloader` is removed. You can use the bootloader
      `Spiral\Nyholm\Bootloader\NyholmBootloader` from the package `spiral/nyholm-bridge` to register PSR-7/PSR-17
      factories.
    -
  Classes `Spiral\Http\Diactoros\ResponseFactory`, `Spiral\Http\Diactoros\ServerRequestFactory`, `Spiral\Http\Diactoros\StreamFactory`,
  `Spiral\Http\Diactoros\UploadedFileFactory`, `Spiral\Http\Diactoros\UriFactory`
  are removed. You can use `spiral/nyholm-bridge` to define PSR-17 factories.
    - [spiral/exceptions] All handlers have been renamed into renderers. `HandlerInterface` has been deleted.
    - [spiral/exceptions] Added `Spiral\Exceptions\Verbosity` enum.
    - [spiral/router] Removed deprecated method `addRoute` in the `Spiral\Router\RouterInterface`
      and `Spiral\Router\Router`.
      Use method `setRoute` instead.
    - [spiral/validation] `Spiral\Validation\Checker\EntityChecker` is removed.
      Use `Spiral\Cycle\Bootloader\ValidationBootloader` with `Spiral\Cycle\Validation\EntityChecker` from
      package `spiral/cycle-bridge`
    - [spiral/validation] Removed deprecated methods `datetime` and `timezone` in the
      `Spiral\Validation\Checker\TypeChecker` class. Use `Spiral\Validation\Checker\DatetimeChecker::valid()` and
      `Spiral\Validation\Checker\DatetimeChecker::timezone()` instead.
    - [spiral/validation] Added return type `array|callable|string` to the method `parseCheck`
      in `Spiral\Validation\ParserInterface` interface.
    - [spiral/validation] Added `array|string|\Closure` parameter type of `$rules` to the method `getRules`
      in `Spiral\Validation\RulesInterface` interface.
    - [spiral/validation] Added `array|\ArrayAccess` parameter type of `$data` to the method `validate`
      in `Spiral\Validation\ValidationInterface` interface.
    - [spiral/validation] Added return type `mixed` to the method `getValue`,
      added `mixed` parameter type of `$default` to the method `getValue`,
      added `mixed` parameter type of `$context` to the method `withContext`,
      added return type `mixed` to the method `getContext` in `Spiral\Validation\ValidatorInterface` interface.
    - [spiral/filters] Added return type `void` and `mixed` parameter type of `$context` to the method `setContext`,
      added return type `mixed` to the method `getContext` in `Spiral\Filters\FilterInterface` interface.
      Added return type `mixed` to the method `getValue` in `Spiral\Filters\InputInterface`.
    - [spiral/dumper] The `Dumper` Component has been removed from the Framework.
    - [spiral/http] Config `Spiral\Config\JsonPayloadConfig` moved to the `Spiral\Bootloader\Http\JsonPayloadConfig`.
    - [spiral/reactor] Added return type `mixed` and `array|string` parameter type of `$search`,
      `array|string` parameter type of `$replace` to the method `replace` in `Spiral\Reactor\ReplaceableInterface`.
    - [spiral/session] Added return type `void` to the method `resume` in `Spiral\Session\SessionInterface`.
    - [spiral/session] Added return type `self` and `mixed` parameter type of `$value` to the method `set`
      in `Spiral\Session\SessionSectionInterface`.
    - [spiral/session] Added return type `bool` to the method `has` in `Spiral\Session\SessionSectionInterface`.
    - [spiral/session] Added return type `mixed` and `mixed` parameter type of `$default` to the method `get`
      in `Spiral\Session\SessionSectionInterface`.
    - [spiral/session] Added return type `mixed` and `mixed` parameter type of `$default` to the method `pull`
      in `Spiral\Session\SessionSectionInterface`.
    - [spiral/session] Added return type `void` to the method `delete` in `Spiral\Session\SessionSectionInterface`.
    - [spiral/session] Added return type `void` to the method `clear` in `Spiral\Session\SessionSectionInterface`.
    - [spiral/pagination] Added return type `self` to the method `limit`, added return type `self` to the
      method `offset`
      in `Spiral\Pagination\PaginableInterface`
    - [spiral/prototype] Parameter `$printer` now is not nullable in `Spiral\Prototype\Injector` constructor.
    - [spiral/models] Added return type `self`, added `mixed` parameter type of `$value` to the method `setField`,
      added return type `mixed`, added `mixed` parameter type of `$default` to the method `getField`,
      added return type `self` to the method `setFields` in `Spiral\Models\EntityInterface`.
    - [spiral/models] Added return type `mixed` to the method `getValue` in `Spiral\Models\ValueInterface`.
    - [spiral/logger] Added return type `self` to the method `addListener`, added return type `void` to the
      method `removeListener`
      in `Spiral\Logger\ListenerRegistryInterface` interface.
    - [spiral/hmvc] Added return type `mixed` to the method `process` in `Spiral\Core\CoreInterceptorInterface`
      interface.
    - [spiral/hmvc] Added return type `mixed` to the method `callAction` in `Spiral\Core\CoreInterface` interface.
    - [spiral/encrypter] Added return type `mixed` to the method `decrypt` in `Spiral\Encrypter\EncrypterInterface`
      interface.
      in `Spiral\DataGrid\InputInterface` interface.
    - [spiral/http] Added return type `array` and `mixed` parameter type of `$filler` to the method `fetch`,
      added return type `mixed` to the method `offsetGet`, added return type `mixed` and `mixed` parameter type
      of `$default` to the method `get`  in `Spiral\Http\Request\InputBag` class.
    - [spiral/config] Added return type `void` to the method `setDefaults` in `Spiral\Config\ConfiguratorInterface`
      interface.
    - [spiral/core] Comprehensive code refactoring. A lot of signatures from `Spiral\Core` namespace has been changed.
      New features:
        - Added supporting for PHP 8.0 Union types.
        - Added supporting for variadic arguments:
            - array passed by parameter name.
                - with named arguments inside.
                - with positional arguments inside.
            - value passed by parameter name.
            - positional trailed values.
        - Support for default object value.
        - Added supporting for referenced parameters in Resolver.
        - The Factory now more strict: no more arguments type conversion.
        - Added the `Spiral\Core\ResolverInterface::validateArguments` method for arguments validation.
        - Support for `WeakReference` bindings.
    - [spiral/boot] Method `starting` renamed to `booting`, method `started` renamed to `booted` in the
      class `Spiral\Boot\AbstractKernel`.
    - [spiral/boot] Added return type `self` to the method `set` in `Spiral\Boot\DirectoriesInterface` interface.
    - [spiral/boot] Added return type `mixed` and `mixed` parameter type of `$default` to the method `get`,
      added in `Spiral\Boot\EnvironmentInterface` interface.
    - [spiral/boot] Added return type `static` to the method `addFinalizer`,
      added return type `void` to the method `finalize` in `Spiral\Boot\FinalizerInterface` interface.
    - [spiral/boot] Added return type `self` to the method `addDispatcher`,
      added return type `mixed` to the method `serve` in `Spiral\Boot\KernelInterface` interface.
    - [spiral/boot] Added `exceptionHandler` parameter in the `Spiral\Boot\AbstractKernel::create` method.
    - [spiral/boot] `Spiral\Boot\AbstractKernel` constructor is protected now.
    - [spiral/boot] Added return type `mixed` to the method `loadData`,
      added return type `void` and `mixed` parameter type of `$data` to the method `saveData`
      in `Spiral\Boot\MemoryInterface` interface.
    - [spiral/boot] In `Bootloaders`, the name of the method has been changed from `boot` to `init`.
      In the code of custom Bootloaders, need to change the name of the method.
    - [spiral/console] Added return type `void` to the method `writeHeader`, added return type `void` to the
      method `execute`,
      method `whiteFooter` renamed to `writeFooter`, added return type `void` to the method `writeFooter`
      in `Spiral\Console\SequenceInterface` interface.
    - [spiral/files] Added return type `bool` to the method `delete`, added return type `bool` to the
      method `deleteDirectory`,
      added return type `bool` to the method `touch`, added return type `bool` to the method `setPermissions`
      in `Spiral\Files\FilesInterface`.
    - [spiral/views] Added return type `mixed` to the method `resolveValue` in `Spiral\Views\ContextInterface`.
    - [spiral/views] Added return type `mixed` to the method `getValue` in `Spiral\Views\DependencyInterface`.
    - [spiral/translator] Added return type `void` to a methods `setLocales`, `saveLocale`
      in `Spiral\Translator\Catalogue\CacheInterface`.
    - [spiral/translator] Added return type `void` to the method `save`
      in `Spiral\Translator\CatalogueManagerInterface`.
    - [spiral/storage] Added `string|\Stringable` parameter type of `$id` to a methods `getContents`, `getStream`,
      `exists`, `getLastModified`, `getSize`, `getMimeType`, `getVisibility`
      in `Spiral\Storage\Storage\ReadableInterface`.
    - [spiral/storage] Added `string|\Stringable` parameter type of `$id` to a methods `create`, `setVisibility`,
      `delete`. Added `string|\Stringable` parameter type of `$id` and `mixed` parameter type of `$content`
      to the method `write`, added `string|\Stringable` parameter type of `$source` and `$destination` to a methods
      `copy`, `move` in `Spiral\Storage\Storage\WritableInterface`.
    - [spiral/stempler] Added return type `mixed` and `mixed` parameter type of `$default` to the method `getAttribute`
      in
      `Spiral\Stempler\Node\AttributedInterface`.
    - [spiral/stempler] Added return type `mixed` and `mixed` parameter type of `$node` to the method `enterNode`,
      added return type `mixed` and `mixed` parameter type of `$node` to the method `leaveNode`
      in `Spiral\Stempler\VisitorInterface`.
    - [spiral/sendit] Dropped support `pipeline` parameter in `mailer` config. Please, use the parameter `queue`
      instead.
    - [spiral/security] Added return type `self` to a methods `addRole`, `removeRole`
      in `Spiral\Security\PermissionsInterface`
    - [spiral/security] Added return type `self` to a methods `set`, `remove` in `Spiral\Security\RulesInterface`
    - [spiral/distribution] Bootloader `Spiral\Bootloader\Distribution\DistributionBootloader` moved to
      the `Spiral\Distribution\Bootloader\DistributionBootloader`,
      config `Spiral\Bootloader\Distribution\DistributionConfig` moved to
      the `Spiral\Distribution\Config\DistributionConfig`.
    - [spiral/storage] Bootloader `Spiral\Bootloader\Storage\StorageBootloader` moved to
      the `Spiral\Storage\Bootloader\StorageBootloader`,
      config `Spiral\Bootloader\Storage\StorageConfig` moved to the `Spiral\Storage\Config\StorageConfig`.
    - [spiral/validation] Bootloader `Spiral\Bootloader\Security\ValidationBootloader` moved to
      the `Spiral\Validation\Bootloader\ValidationBootloader`.
    - [spiral/views] Bootloader `Spiral\Bootloader\Views\ViewsBootloader` moved to
      the `Spiral\Views\Bootloader\ViewsBootloader`.
    - [spiral/boot] By default, overwriting of environment variable values is disabled, the default value
      for `$overwrite`
      changed from `true` to `false` in the `Spiral\Boot\Environment`.
    - [spiral/queue] Removed method `pushCallable` in `Spiral\Queue\QueueTrait`.
    - [spiral/dotenv-bridge] Bootloader `Spiral\DotEnv\Bootloader\DotenvBootloader` must be moved from the `LOAD`
      section to the
      `SYSTEM` section in the application `App.php` file.
- **Medium Impact Changes**
    - A minimal version of `PHP` increased to `^8.1`
    - A minimal version of `symfony/finder` increased to `^5.3`
    - A minimal version of `league/flysystem` increased to `^2.3`
    - A minimal version of `symfony/console` increased to `^6.0`
    - `Spiral\Snapshots\FileSnapshooter` uses `Verbosity` enum instead of int flag.
    - `Spiral\Snapshots\FileSnapshooter` uses `ExceptionRendererInterface $renderer` instead
      of `HandlerInterface $handler`.
    - `Spiral\Snapshots\SnapshotterInterface` usage replaced with `Spiral\Exceptions\ExceptionReporterInterface` in all
      classes.
    - Removed `bin/spiral`. Uses the `spiral/roadrunner-cli` package instead.
- **Other Features**
    - [spiral/queue] Added queue interceptors.
    - [spiral/debug] Added `Spiral\Debug\StateConsumerInterface`.
    - [spiral/boot] Added new `boot` method in `Bootloaders`. It will be executed after the `init` method is executed in
      all `Bootloaders`.
      The old `boot` method has been renamed to `init`. See **High Impact Changes** section.
    - [spiral/boot] Added automatic booting of `Bootloaders` requested in the `init` and `boot` methods.
      They no longer need to be specified explicitly in `DEPENDENCIES` property or in `defineDependencies` method.
    - [spiral/monolog-bridge] Added the ability to configure the default channel using the configuration file or
      environment variable `MONOLOG_DEFAULT_CHANNEL`.
    - [spiral/serializer] Added a new spiral/serializer component. Contains an interface and a minimal implementation
      that can be extended by external serializers.
    - [spiral/queue] Added the ability to configure serializers for different types of jobs.
    - Added class `Spiral\Exceptions\Reporter\FileReporter`, which
      implements `Spiral\Exceptions\ExceptionReporterInterface`
      and can create text files with information about an exception.

## v2.14.0 - Unreleased

- **High Impact Changes**
- **Medium Impact Changes**
- **Low Impact Changes**
- **Other Features**
- **Bug Fixes**

## v2.13.0 - 2022-04-28

- **Medium Impact Changes**
    - Dispatcher `Spiral\Http\SapiDispatcher` is deprecated. Will be moved to `spiral/sapi-bridge` and removed in v3.0
    -
  Classes `Spiral\Http\Emitter\SapiEmitter`, `Spiral\Http\Exception\EmitterException`, `Spiral\Http\EmitterInterface`,
  `Spiral\Http\SapiRequestFactory` is deprecated. Will be removed in version v3.0.
  After the release of v3.0, must use the package `spiral/sapi-bridge` for SAPI functionality.
    - The `dumper` component is deprecated and will be removed in v3.0
- **Other Features**
    - [spiral/http] Added parameter `chunkSize` in the `http` configuration file.
    - [spiral/queue] Added attribute `Queueable` to mark classes that can be queued.
      Added `Spiral\Queue\QueueableDetector` class to easily check if an object should be queued or not and get the
      queue
      from an attribute or getQueue method on the object.
    - [spiral/broadcasting] New component with common interfaces (RR2.0 support)

## v2.12.0 - 2022-04-07

- **Medium Impact Changes**
    - Bootloaders `Spiral\Bootloader\Broadcast\BroadcastBootloader`, `Spiral\Bootloader\Http\WebsocketsBootloader`
      are deprecated. Will be removed in v3.0.
    - Console commands `Spiral\Command\Database\ListCommand`, `Spiral\Command\Database\TableCommand`,
      `Spiral\Command\GRPC\GenerateCommand`, `Spiral\Command\GRPC\ListCommand`, `Spiral\Command\Migrate\AbstractCommand`,
      `Spiral\Command\Migrate\InitCommand`, `Spiral\Command\Migrate\MigrateCommand`, `Spiral\Command\Migrate\ReplayCommand`,
      `Spiral\Command\Migrate\RollbackCommand`, `Spiral\Command\Migrate\StatusCommand` are deprecated. Will be removed
      in v3.0.
    - Classes `Spiral\Broadcast\Config\WebsocketsConfig`, `Spiral\Broadcast\Middleware\WebsocketsMiddleware`,
      `Spiral\GRPC\Exception\CompileException`, `Spiral\GRPC\GRPCDispatcher`, `Spiral\GRPC\LocatorInterface`,
      `Spiral\GRPC\ProtoCompiler`, `Spiral\GRPC\ServiceLocator`, `Spiral\Http\LegacyRrDispatcher`, `Spiral\Http\RrDispatcher`
      are deprecated. Will be removed in v3.0.
    - Changed package replacement strategy. "*" is replaced by "self.version".
    - Sapi emitter now supports streaming emitting.
    - [spiral/data-grid-bridge] Removed deprecation in
      classes `Spiral\DataGrid\Annotation\DataGrid`, `Spiral\DataGrid\Bootloader\GridBootloader`,
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
    - [spiral/session] Added `Spiral\Session\SessionFactoryInterface`. Now you can use custom implementation of
      sessions.
    - [spiral/scaffolder] Console
      commands `Spiral\Scaffolder\Command\MigrationCommand`, `Spiral\Scaffolder\Command\Database\RepositoryCommand`,
      `Spiral\Scaffolder\Command\Database\EntityCommand` is deprecated. Will be moved to `spiral/cycle-bridge` and
      removed in v3.0
    - [spiral/scaffolder] Scaffolder `Spiral\Scaffolder\Declaration\MigrationDeclaration` is deprecated. Will be moved
      to `spiral/cycle-bridge`
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
      `Spiral\Scaffolder\Declaration\Database\RepositoryDeclaration` is deprecated. Will be moved
      to `spiral/cycle-bridge`
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
