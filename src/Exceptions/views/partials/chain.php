<?php
/**
 * @var array                           $stacktrace
 * @var Throwable                       $exception
 * @var \Spiral\Exceptions\ValueWrapper $valueWrapper
 */
foreach ($stacktrace as $index => $trace) {
    if (empty($trace['file']) && isset($stacktrace[$index - 1]['file'])) {
        $trace['file'] = $stacktrace[$index - 1]['file'];
        $trace['line'] = $stacktrace[$index - 1]['line'];
    }

    if (!isset($stacktrace[$index + 1])) {
        $trace['file'] = $exception->getFile();
        $trace['line'] = $exception->getLine();
    }

    if (!isset($trace['function']) || !isset($trace['file'])) {
        continue;
    }

    $function = '<strong>' . $trace['function'] . '</strong>';
    if (isset($trace['type']) && isset($trace['class'])) {
        $reflection = new ReflectionClass($trace['class']);
        $function = sprintf(
            '<span title="%s">%s</span>%s%s',
            $reflection->getName(),
            $reflection->getShortName(),
            $trace['type'],
            $function
        );
    }

    $args = [];
    if (isset($trace['args'])) {
        $args = $valueWrapper->wrap($trace['args']);
    } ?>
    <div class="call">
        <div class="function"><?= $function ?>(<span class="arguments"><?= implode(', ', $args) ?></span>)</div>
        <div class="location"><i><?= $trace['file'] ?></i> at <strong>line <?= $trace['line'] ?></strong></div>
    </div>
<?php } ?>
