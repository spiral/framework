<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Console;

use Spiral\Core\Component;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class AskHelper extends Component
{
    /**
     * Default value for max attempts.
     */
    const MAX_ATTEMPTS = 5;

    /**
     * QuestionHelper instance.
     *
     * @var QuestionHelper
     */
    protected $helper = null;

    /**
     * OutputInterface is the interface implemented by all Output classes.
     *
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * InputInterface is the interface implemented by all input classes.
     *
     * @var InputInterface
     */
    protected $input = null;

    /**
     * Default value for max attempts.
     *
     * @var int
     */
    protected $maxAttempts = 5;

    /**
     * Hidden input.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * Sets whether to fallback on non-hidden question if the response can not be hidden.
     *
     * @var bool
     */
    protected $hiddenFallback = null;

    /**
     * Helper class aggregates set of ask functions.
     *
     * @param QuestionHelper  $helper Parent command.
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    public function __construct(QuestionHelper $helper, InputInterface $input, OutputInterface $output)
    {
        $this->helper = $helper;
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Update maxAttempts value.
     *
     * @param int $maxAttempts
     * @return $this
     */
    public function maxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    /**
     * Mark question input as hidden (no value will be displayed while entering).
     *
     * @param bool $hidden
     * @param bool $fallback Hidden fallback.
     * @return $this
     */
    public function hidden($hidden = true, $fallback = true)
    {
        $this->hidden = $hidden;
        $this->hiddenFallback = $fallback;

        return $this;
    }

    /**
     * Get user input.
     *
     * @param string $question     Question to ask before input.
     * @param mixed  $defaultValue Default value.
     * @return mixed
     */
    public function input($question, $defaultValue = null)
    {
        return $this->dispatch(new Question("<question>{$question}</question> ", $defaultValue));
    }

    /**
     * Get user confirmation.
     *
     * @param string $question     Question to ask for confirmation.
     * @param mixed  $defaultValue Default value.
     * @return mixed
     */
    public function confirm($question, $defaultValue = null)
    {
        return $this->dispatch(
            new ConfirmationQuestion("<question>{$question}</question> ", $defaultValue)
        );
    }

    /**
     * Get user selection.
     *
     * @param string $question     Question to ask before input.
     * @param array  $select       List of selection values.
     * @param mixed  $defaultValue Default value.
     * @return mixed
     */
    public function choice($question, array $select, $defaultValue = null)
    {
        $question = new ChoiceQuestion("<question>{$question}</question> ", $select, $defaultValue);

        return $this->dispatch($question);
    }

    /**
     * Configure question with fluent values and dispatch it.
     *
     * @param Question $question
     * @return mixed
     */
    protected function dispatch(Question $question)
    {
        $question->setMaxAttempts($this->maxAttempts);
        if ($this->hidden)
        {
            $question->setHidden($this->hidden)->setHiddenFallback($this->hiddenFallback);
        }

        $this->maxAttempts = self::MAX_ATTEMPTS;
        $this->hidden = false;
        $this->hiddenFallback = true;

        return $this->helper->ask($this->input, $this->output, $question);
    }
}