<?php

declare(strict_types=1);

namespace Spiral\Scaffolder\Declaration;

use Nette\PhpGenerator\Property;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Filters\Attribute\Input;
use Spiral\Filters\Attribute\Input\AbstractInput;
use Spiral\Filters\Attribute\Input\BearerToken;
use Spiral\Filters\Attribute\Input\Cookie;
use Spiral\Filters\Attribute\Input\Data;
use Spiral\Filters\Attribute\Input\File;
use Spiral\Filters\Attribute\Input\Header;
use Spiral\Filters\Attribute\Input\Method;
use Spiral\Filters\Attribute\Input\Path;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\Attribute\Input\RemoteAddress;
use Spiral\Filters\Attribute\Input\Route;
use Spiral\Filters\Attribute\Input\Server;
use Spiral\Filters\Attribute\Input\Uri;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Scaffolder\Config\ScaffolderConfig;
use Spiral\Validation\Config\ValidationConfig;
use Spiral\Validation\Exception\ValidationException;

class FilterDeclaration extends AbstractDeclaration implements HasInstructions
{
    public const TYPE = 'filter';
    private readonly ?ValidationConfig $validationConfig;

    public function __construct(
        ContainerInterface $container,
        ScaffolderConfig $config,
        string $name,
        ?string $comment = null,
        ?string $namespace = null,
    ) {
        parent::__construct($config, $name, $comment, $namespace);

        try {
            $this->validationConfig = $container->get(ValidationConfig::class);
        } catch (\Throwable) {
            // Validation is not configured
            $this->validationConfig = null;
        }
    }

    public function declare(): void
    {
        $this->namespace->addUse(Filter::class);
        $this->class->setExtends(Filter::class);

        $this->class->setFinal();
    }

    public function addFilterDefinition(): void
    {
        $validation = $this->validationConfig?->getDefaultValidator();
        if ($validation === null) {
            throw new ValidationException(
                'Default Validator is not configured. Read more at https://spiral.dev/docs/validation-factory'
            );
        }

        if (!\class_exists($validation)) {
            throw new ValidationException(
                \sprintf(
                    'Default Validator "%s" is not class or does not exist.',
                    $validation,
                )
            );
        }

        $this->namespace->addUse(HasFilterDefinition::class);
        $this->namespace->addUse(FilterDefinitionInterface::class);
        $this->namespace->addUse($validation);
        $this->class->addImplement(HasFilterDefinition::class);

        $validationClass = (new \ReflectionClass($validation))->getShortName();

        $this->class->addMethod('filterDefinition')
            ->setReturnType(FilterDefinitionInterface::class)
            ->setBody(
                <<<PHP
return new $validationClass(validationRules: [
    // Put your validation rules here
]);
PHP,
            );
    }

    public function addProperty(string $property): void
    {
        $property = $this->parseProperty($property);

        $p = $this->class->addProperty($property->getName())
            ->setType($property->getType());

        foreach ($property->getAttributes() as $attribute) {
            $p->addAttribute($attribute->getName(), $attribute->getArguments());
        }
    }

    public function getInstructions(): array
    {
        return [
            'Read more about Filter Objects in the documentation: https://spiral.dev/docs/filters-filter',
            'Read more about Filter validation handling here: https://spiral.dev/docs/filters-filter#handle-validation-errors',
        ];
    }

    private function parseProperty(string $property): Property
    {
        $declaredType = null;

        if (\str_contains($property, ':')) {
            $segments = \explode(':', $property, 3);

            if (\count($segments) === 3) {
                [$property, $source, $declaredType] = $segments;
            } else {
                [$property, $source] = $segments;
            }
        }

        [$source, $type] = $this->getSourceClassByName($source ?? 'input');

        $this->namespace->addUse($source);

        return (new Property($property))
            ->setPublic()
            ->setType($declaredType ?? $type)
            ->addAttribute($source, ['key' => $property]);
    }

    /**
     * @return array{0: class-string<AbstractInput>, 1: non-empty-string}
     */
    private function getSourceClassByName(string $source): array
    {
        return match ($source) {
            'data' => [Data::class, 'string'],
            'post' => [Post::class, 'string'],
            'query' => [Query::class, 'string'],
            'file' => [File::class, UploadedFileInterface::class],
            'cookie' => [Cookie::class, 'string'],
            'header' => [Header::class, 'string'],
            'method' => [Method::class, 'string'],
            'path' => [Path::class, 'string'],
            'ip' => [RemoteAddress::class, 'string'],
            'route' => [Route::class, 'string'],
            'server' => [Server::class, 'string'],
            'uri' => [Uri::class, UriInterface::class],
            'token' => [BearerToken::class, 'string'],
            default => [Input\Input::class, 'string']
        };
    }
}
