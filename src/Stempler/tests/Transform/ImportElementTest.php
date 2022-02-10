<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler\Transform;

use Spiral\Stempler\Builder;
use Spiral\Stempler\Directive\ConditionalDirective;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Loader\LoaderInterface;
use Spiral\Stempler\Loader\StringLoader;
use Spiral\Stempler\Transform\Finalizer\DynamicToPHP;
use Spiral\Stempler\Transform\Import\Element;
use Spiral\Stempler\Transform\Merge\ResolveImports;
use Spiral\Stempler\Transform\Visitor\DefineAttributes;
use Spiral\Stempler\Transform\Visitor\DefineBlocks;

class ImportElementTest extends BaseTest
{
    public function testNoImport(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set('root', '<url href="google.com">hello world</url>');
        $loader->set('import', '<a href="${href}">${context}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<url href="google.com">hello world</url>',
            $builder->compile('root')->getContent()
        );
    }

    public function testSimpleImport(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="google.com">hello world</url>'
        );
        $loader->set('import', '<a href="${href}"><block:context/></a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="google.com">hello world</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testImportWithPHP(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="<?php echo \'google.com\'?>">hello world</url>'
        );
        $loader->set('import', '<a href="${href}"><block:context/></a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo \'google.com\'?>">hello world</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testImportWithOutput(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}">hello world</url>'
        );
        $loader->set('import', '<a href="${href}"><block:context/></a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES |'
            . ' ENT_SUBSTITUTE, \'utf-8\'); ?>">hello world</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testStringValueIntoPHP(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}">hello world</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'context\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper(\'hello world\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testOutputValueIntoPHPFromAttribute(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="<?php echo \'bad\'?>">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper(\'bad\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testOutputValueIntoPHPFromAttributeUsingOutput(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="{{ \'OK\' }}">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper(\'OK\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testValueIntoPHPFromMultiValue(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="hello {{ \'OK\' }}">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper(\'hello \'.\'OK\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testValueIntoPHPFromMultiValueWithSpacing(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}" value="{{ \'OK\' }}  {{ \'cool\' }}">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper(\'OK\'.\' '
            . ' \'.\'cool\'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testValueIntoPHPFromMultiValueWithSpacingAround(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/>'
            . '<url href="{{ $url }}" value=" {{ \'OK\' }} {{ \'cool\' }} ">abc</url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'value\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper'
            . '(\' \'.\'OK\'.\' \'.\'cool\'.\' \'), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testDefaultPHPValue(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}"></url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'value\', \'default\'.\'xxx\')) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper(inject(\'value\', '
            . '\'default\'.\'xxx\')), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testDefaultPHPValueArray(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="url"/><url href="{{ $url }}"></url>'
        );
        $loader->set('import', '<a href="${href}">{{ strtoupper(inject(\'value\', [\'abc\'])) }}</a>');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<a href="<?php echo htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>">'
            . '<?php echo htmlspecialchars((string) strtoupper(inject(\'value\', '
            . '[\'abc\'])), ENT_QUOTES | ENT_SUBSTITUTE, \'utf-8\'); ?>'
            . '</a>',
            $builder->compile('root')->getContent()
        );
    }

    public function testHasInjectionEmpty(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="opt"/><opt>hello world</opt>'
        );

        $loader->set('import', '@if(injected(\'header\'))<div class="header">${header}</div>@endif${context}');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<?php if(false): ?>'
            . '<div class="header"></div>'
            . '<?php endif; ?>'
            . 'hello world',
            $builder->compile('root')->getContent()
        );
    }

    public function testHasInjection(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="opt"/><opt><block:header>abc</block:header>hello world</opt>'
        );

        $loader->set('import', '@if(injected(\'header\'))<div class="header">${header}</div>@endif${context}');

        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<?php if(true): ?>'
            . '<div class="header">abc</div>'
            . '<?php endif; ?>'
            . 'hello world',
            $builder->compile('root')->getContent()
        );
    }

    public function testParentBlock(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="h"/><h><block:c>a<block:parent/></block:c></h>'
        );
        $loader->set('import', '<x c="${c|b}"></x>');

        $builder = $this->getBuilder($loader, []);
        $this->assertSame(
            '<x c="ab"></x>',
            $builder->compile('root')->getContent()
        );
    }

    public function testParentBlockShort(): void
    {
        $loader = $loader ?? new StringLoader();
        $loader->set(
            'root',
            '<use:element path="import" as="h"/><h c="a ${parent}"/>'
        );
        $loader->set('import', '<x c="${c|b}"></x>');


        $builder = $this->getBuilder($loader, []);

        $this->assertSame(
            '<x c="a b"></x>',
            $builder->compile('root')->getContent()
        );
    }

    public function testElementPathAndAlias(): void
    {
        $element = new Element('path/to/import');
        $this->assertSame('path/to/import', $element->getPath());
        $this->assertSame('import', $element->getAlias());
    }

    protected function getBuilder(LoaderInterface $loader, array $visitors): Builder
    {
        $builder = parent::getBuilder($loader, $visitors);
        $builder->addVisitor(new ResolveImports($builder), Builder::STAGE_TRANSFORM);

        // so we can inject into PHP
        $dynamic = new DynamicToPHP();
        $dynamic->addDirective(new LoopDirective());
        $dynamic->addDirective(new ConditionalDirective());

        $builder->addVisitor($dynamic, Builder::STAGE_FINALIZE);

        return $builder;
    }

    protected function getVisitors(): array
    {
        return [
            new DefineAttributes(),
            new DefineBlocks()
        ];
    }
}
