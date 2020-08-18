<?php
/**
 * @var array                           $stacktrace
 * @var Throwable                       $exception
 * @var \Spiral\Exceptions\ValueWrapper $valueWrapper
 * @var \Spiral\Exceptions\Highlighter  $highlighter
 * @var bool                            $showSource
 */
$vendorDir = (new ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName();
$vendorDir = dirname(dirname($vendorDir));
$vendorID = null;
$vendorCount = 0;
foreach ($stacktrace as $index => $trace) {
    $args = [];
    if (isset($trace['args'])) {
        $args = $valueWrapper->wrap($trace['args']);
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

    if (!isset($trace['file']) || !file_exists($trace['file'])) { ?>
        <div class="container no-trace">
            <?= $function ?>(<span class="arguments"><?= join(', ', $args) ?></span>)
        </div>
        <?php
        continue;
    }

    $isVendor = strpos($trace['file'], $vendorDir) === 0 && $index > 1;
    if ($isVendor) {
        if ($vendorID === null) {
            $vendorID = $index;
            $vendorCount++;
            echo sprintf('<span id="hidden-trace-%s" style="display: none;">', $vendorID);
        } else {
            $vendorCount++;
        }
    } elseif ($vendorID !== null) {
        echo '</span>';
        echo sprintf(
            '<div id="%s" class="container" style="cursor: pointer;" onclick="toggle(\'%s\'); toggle(\'%s\');">&plus; %s vendor frame(s)...</div>',
            'toggle-trace-' . $vendorID,
            'toggle-trace-' . $vendorID,
            'hidden-trace-' . $vendorID,
            $vendorCount
        );
        $vendorID = null;
        $vendorCount = 0;
    }
    ?>
    <div class="container">
        <div class="location">
            <?= $function ?>(<span class="arguments"><?= join(', ', $args) ?></span>)<br/>
            <em>In&nbsp;<?= $trace['file'] ?>&nbsp;at&nbsp;<strong>line <?= $trace['line'] ?></strong></em>
        </div>
        <?php if ($showSource && file_exists($trace['file'])) : ?>
            <div class="lines">
                <?= $highlighter->highlightLines(file_get_contents($trace['file']), $trace['line']) ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

if ($vendorID !== null) {
    echo '</span>';
}
