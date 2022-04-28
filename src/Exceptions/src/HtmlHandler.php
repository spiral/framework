<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

use Spiral\Debug\Dumper;
use Spiral\Debug\Renderer\HtmlRenderer;
use Spiral\Debug\StateInterface;
use Spiral\Exceptions\Style\HtmlStyle;

/**
 * Render exception information into html.
 *
 * @deprecated since v2.13. Will be removed in v3.0
 */
class HtmlHandler extends AbstractHandler
{
    /**
     * Visual styles.
     */
    public const DEFAULT  = 'default';
    public const INVERTED = 'inverted';

    /** @var HtmlRenderer */
    protected $renderer;

    /** @var Highlighter */
    protected $highlighter;

    /** @var string */
    protected $style = self::DEFAULT;

    /** @var Dumper */
    protected $dumper;

    /** @var StateInterface|null */
    protected $state;

    public function __construct(string $style = self::DEFAULT)
    {
        $this->style = $style;
        $this->dumper = new Dumper();

        if ($style == self::INVERTED) {
            $this->renderer = new HtmlRenderer(HtmlRenderer::INVERTED);
            $this->highlighter = new Highlighter(new HtmlStyle(HtmlStyle::INVERTED));
        } else {
            $this->renderer = new HtmlRenderer(HtmlRenderer::DEFAULT);
            $this->highlighter = new Highlighter(new HtmlStyle(HtmlStyle::DEFAULT));
        }

        $this->dumper->setRenderer(Dumper::RETURN, $this->renderer);
    }

    public function withState(StateInterface $state): HandlerInterface
    {
        $handler = clone $this;
        $handler->state = $state;

        return $handler;
    }

    /**
     * @inheritdoc
     */
    public function renderException(\Throwable $e, int $verbosity = self::VERBOSITY_BASIC): string
    {
        $options = [
            'message'      => $this->getMessage($e),
            'exception'    => $e,
            'valueWrapper' => new ValueWrapper($this->dumper, $this->renderer, $verbosity),
            'style'        => $this->render('styles/' . $this->style),
            'footer'       => $this->render('partials/footer'),
            'variables'    => '',
            'logs'         => '',
            'tags'         => '',
        ];

        $options['stacktrace'] = $this->render('partials/stacktrace', [
            'exception'    => $e,
            'stacktrace'   => $this->getStacktrace($e),
            'dumper'       => $this->dumper,
            'renderer'     => $this->renderer,
            'highlighter'  => $this->highlighter,
            'valueWrapper' => $options['valueWrapper'],
            'showSource'   => $verbosity >= self::VERBOSITY_VERBOSE,
        ]);

        $options['chain'] = $this->render('partials/chain', [
            'exception'    => $e,
            'stacktrace'   => $this->getStacktrace($e),
            'valueWrapper' => $options['valueWrapper'],
        ]);

        if ($this->state !== null) {
            if ($this->state->getTags() !== []) {
                $options['tags'] = $this->render('partials/tags', [
                    'tags' => $this->state->getTags(),
                ]);
            }

            if ($this->state->getLogEvents() !== []) {
                $options['logs'] = $this->render('partials/logs', [
                    'logEvents' => $this->state->getLogEvents(),
                ]);
            }

            if ($this->state->getVariables() !== []) {
                $options['variables'] = $this->render('partials/variables', [
                    'variables' => $this->state->getVariables(),
                ]);
            }
        }

        return $this->render('exception', $options);
    }

    /**
     * Render PHP template.
     */
    private function render(string $view, array $options = []): string
    {
        extract($options, EXTR_OVERWRITE);

        ob_start();
        require $this->getFilename($view);

        return ob_get_clean();
    }

    /**
     * Get view filename.
     */
    private function getFilename(string $view): string
    {
        return sprintf('%s/views/%s.php', dirname(__DIR__), $view);
    }
}
