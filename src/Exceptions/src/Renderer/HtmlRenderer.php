<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Renderer;

use Spiral\Core\ContainerScope;
use Spiral\Debug\Dumper;
use Spiral\Debug\Renderer\HtmlRenderer as DebugRenderer;
use Spiral\Debug\StateConsumerInterface;
use Spiral\Debug\StateInterface;
use Spiral\Exceptions\Style\HtmlStyle;
use Spiral\Exceptions\Verbosity;

/**
 * Render exception information into html.
 */
final class HtmlRenderer extends AbstractRenderer implements StateConsumerInterface
{
    /**
     * Visual styles.
     */
    public const DEFAULT  = 'default';
    public const INVERTED = 'inverted';
    protected const FORMATS = ['text/html', 'html'];

    protected DebugRenderer $renderer;
    protected Highlighter $highlighter;
    protected Dumper $dumper;
    protected ?StateInterface $state = null;

    public function __construct(
        protected string $style = self::DEFAULT
    ) {
        $this->dumper = new Dumper();

        if ($style === self::INVERTED) {
            $this->renderer = new DebugRenderer(DebugRenderer::INVERTED);
            $this->highlighter = new Highlighter(new HtmlStyle(HtmlStyle::INVERTED));
        } else {
            $this->renderer = new DebugRenderer(DebugRenderer::DEFAULT);
            $this->highlighter = new Highlighter(new HtmlStyle(HtmlStyle::DEFAULT));
        }

        $this->dumper->setRenderer(Dumper::RETURN, $this->renderer);
    }

    public function withState(StateInterface $state): static
    {
        $handler = clone $this;
        $handler->state = $state;

        return $handler;
    }

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = null,
        string $format = null
    ): string {
        $renderer = $this;
        // getting state if possible
        if ($this->state === null) {
            $container = ContainerScope::getContainer();
            try {
                $state = $container?->get(StateInterface::class);
            } catch (\Throwable) {
                $state = null;
            }
            if ($state !== null) {
                $renderer = $this->withState($state);
            }
        }

        $verbosity ??= $renderer->defaultVerbosity;
        $options = [
            'message'      => $renderer->getMessage($exception),
            'exception'    => $exception,
            'valueWrapper' => new ValueWrapper($renderer->dumper, $renderer->renderer, $verbosity),
            'style'        => $renderer->renderTemplate('styles/' . $renderer->style),
            'footer'       => $renderer->renderTemplate('partials/footer'),
            'variables'    => '',
            'logs'         => '',
            'tags'         => '',
        ];

        $options['stacktrace'] = $renderer->renderTemplate('partials/stacktrace', [
            'exception'    => $exception,
            'stacktrace'   => $renderer->getStacktrace($exception),
            'dumper'       => $renderer->dumper,
            'renderer'     => $renderer->renderer,
            'highlighter'  => $renderer->highlighter,
            'valueWrapper' => $options['valueWrapper'],
            'showSource'   => $verbosity->value >= Verbosity::VERBOSE->value,
        ]);

        $options['chain'] = $renderer->renderTemplate('partials/chain', [
            'exception'    => $exception,
            'stacktrace'   => $renderer->getStacktrace($exception),
            'valueWrapper' => $options['valueWrapper'],
        ]);

        if ($renderer->state !== null) {
            if ($renderer->state->getTags() !== []) {
                $options['tags'] = $renderer->renderTemplate('partials/tags', [
                    'tags' => $renderer->state->getTags(),
                ]);
            }

            if ($renderer->state->getLogEvents() !== []) {
                $options['logs'] = $renderer->renderTemplate('partials/logs', [
                    'logEvents' => $renderer->state->getLogEvents(),
                ]);
            }

            if ($renderer->state->getVariables() !== []) {
                $options['variables'] = $renderer->renderTemplate('partials/variables', [
                    'variables' => $renderer->state->getVariables(),
                ]);
            }
        }

        return $renderer->renderTemplate('exception', $options);
    }

    /**
     * Prepared exception message.
     */
    private function getMessage(\Throwable $e): string
    {
        return \sprintf('%s: %s in %s at line %s', $e::class, $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * Render PHP template.
     */
    private function renderTemplate(string $view, array $options = []): string
    {
        \extract($options, EXTR_OVERWRITE);

        \ob_start();
        require $this->getFilename($view);

        return \ob_get_clean();
    }

    /**
     * Get view filename.
     */
    private function getFilename(string $view): string
    {
        return \sprintf('%s/views/%s.php', \dirname(__DIR__, 2), $view);
    }
}
