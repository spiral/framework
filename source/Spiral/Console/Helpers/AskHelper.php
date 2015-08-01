<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Console\Helpers;

use Spiral\Core\Component;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * AskHelper provides simplified access to QuestionHelper functionality using short methods.
 */
class AskHelper extends Component
{
    /**
     * Default value for max attempts.
     */
    const MAX_ATTEMPTS = 3;

    /**
     * @var QuestionHelper
     */
    private $helper = null;

    /**
     * @var OutputInterface
     */
    private $output = null;

    /**
     * @var InputInterface
     */
    private $input = null;

    /**
     * Default value for max attempts.
     *
     * @var int
     */
    private $maxAttempts = self::MAX_ATTEMPTS;

    /**
     * Mark question as hidden.
     *
     * @var bool
     */
    private $hidden = false;

    /**
     * Sets whether to fallback on non-hidden question if the response can not be hidden.
     *
     * @var bool
     */
    private $hiddenFallback = null;

    /**
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
     * Change maxAttempts value.
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
     * Request user input with specified question and default value.
     *
     * @param string $question
     * @param mixed  $default
     * @return mixed
     */
    public function input($question, $default = null)
    {
        return $this->dispatch(new Question("<question>{$question}</question> ", $default));
    }

    /**
     * Request user confirmation of given question.
     *
     * @param string $question
     * @param mixed  $default
     * @return mixed
     */
    public function confirm($question, $default = null)
    {
        return $this->dispatch(
            new ConfirmationQuestion("<question>{$question}</question> ", $default)
        );
    }

    /**
     * Request user selection from given options list.
     *
     * @param string $question
     * @param array  $options
     * @param mixed  $default
     * @return mixed
     */
    public function choice($question, array $options, $default = null)
    {
        $question = new ChoiceQuestion("<question>{$question}</question> ", $options, $default);

        return $this->dispatch($question);
    }

    /**
     * Configure and dispatch question.
     *
     * @param Question $question
     * @return mixed
     */
    private function dispatch(Question $question)
    {
        $question->setMaxAttempts($this->maxAttempts);

        if ($this->hidden)
        {
            $question->setHidden($this->hidden)->setHiddenFallback($this->hiddenFallback);
        }

        //Reset options
        $this->maxAttempts = self::MAX_ATTEMPTS;
        $this->hidden = false;
        $this->hiddenFallback = true;

        return $this->helper->ask($this->input, $this->output, $question);
    }
}