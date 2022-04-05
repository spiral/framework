<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Renderer;

use Spiral\Debug\Dumper;
use Spiral\Debug\Renderer\HtmlRenderer as DebugRenderer;
use Spiral\Debug\StateInterface;
use Spiral\Exceptions\Style\HtmlStyle;
use Spiral\Exceptions\Verbosity;

/**
 * Render exception information into html.
 */
final class HtmlRenderer extends AbstractRenderer
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

        if ($style == self::INVERTED) {
            $this->renderer = new DebugRenderer(DebugRenderer::INVERTED);
            $this->highlighter = new Highlighter(new HtmlStyle(HtmlStyle::INVERTED));
        } else {
            $this->renderer = new DebugRenderer(DebugRenderer::DEFAULT);
            $this->highlighter = new Highlighter(new HtmlStyle(HtmlStyle::DEFAULT));
        }

        $this->dumper->setRenderer(Dumper::RETURN, $this->renderer);
    }

    public function withState(StateInterface $state): self
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
        $verbosity ??= $this->defaultVerbosity;
        $options = [
            'message'      => $this->getMessage($exception),
            'exception'    => $exception,
            'valueWrapper' => new ValueWrapper($this->dumper, $this->renderer, $verbosity),
            'style'        => $this->renderTemplate('styles/' . $this->style),
            'footer'       => $this->renderTemplate('partials/footer'),
            'variables'    => '',
            'logs'         => '',
            'tags'         => '',
        ];

        $options['stacktrace'] = $this->renderTemplate('partials/stacktrace', [
            'exception'    => $exception,
            'stacktrace'   => $this->getStacktrace($exception),
            'dumper'       => $this->dumper,
            'renderer'     => $this->renderer,
            'highlighter'  => $this->highlighter,
            'valueWrapper' => $options['valueWrapper'],
            'showSource'   => $verbosity->value >= Verbosity::VERBOSE->value,
        ]);

        $options['chain'] = $this->renderTemplate('partials/chain', [
            'exception'    => $exception,
            'stacktrace'   => $this->getStacktrace($exception),
            'valueWrapper' => $options['valueWrapper'],
        ]);

        if ($this->state !== null) {
            if ($this->state->getTags() !== []) {
                $options['tags'] = $this->renderTemplate('partials/tags', [
                    'tags' => $this->state->getTags(),
                ]);
            }

            if ($this->state->getLogEvents() !== []) {
                $options['logs'] = $this->renderTemplate('partials/logs', [
                    'logEvents' => $this->state->getLogEvents(),
                ]);
            }

            if ($this->state->getVariables() !== []) {
                $options['variables'] = $this->renderTemplate('partials/variables', [
                    'variables' => $this->state->getVariables(),
                ]);
            }
        }

        return $this->renderTemplate('exception', $options);
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
