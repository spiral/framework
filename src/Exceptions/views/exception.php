<?php
/**
 * @var Throwable                         $exception
 * @var string                            $message
 * @var string                            $style
 * @var string                            $variables
 * @var string                            $footer
 * @var string                            $stacktrace
 * @var string                            $chain
 * @var string                            $logs
 * @var string                            $tags
 * @var \Spiral\Exceptions\ValueWrapper   $valueWrapper
 * @var \Spiral\Debug\StateInterface|null $state
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= $message ?></title>
    <?= $style ?>
</head>
<body class="spiral-exception">
<a name="dump-top"></a>
<div class="wrapper">
    <div class="header">
        <?= get_class($exception) ?>: <strong><?= $exception->getMessage() ?></strong>
        in&nbsp;<i><?= $exception->getFile() ?></i>&nbsp;at&nbsp;<strong>line&nbsp;<?= $exception->getLine() ?></strong>
        <?php
        $prev = $exception->getPrevious();
        while ($prev instanceof Throwable) : ?>
            <div class="previous">
                &bull; caused by <?= get_class($prev) ?>: <strong><?= $prev->getMessage() ?></strong>
                in&nbsp;<i><?= $prev->getFile() ?></i>&nbsp;at&nbsp;<strong>line&nbsp;<?= $prev->getLine() ?></strong>
            </div>
            <?php $prev = $prev->getPrevious(); endwhile; ?>
    </div>
    <div class="stacktrace">
        <div class="trace">
            <?= $stacktrace ?>
        </div>
        <div class="chain">
            <div class="dumper" id="argument-dumper"></div>
            <div class="calls">
                <?= $chain ?>
            </div>
        </div>
    </div>
    <?php
    foreach ($valueWrapper->getValues() as $id => $content) {
        echo "<div id=\"argument-{$id}\" style=\"display: none\">{$content}</div>";
    }
    ?>
    <?= $tags ?>
    <?= $logs ?>
    <?= $variables ?>
    <?= $footer ?>
</div>
<script type="text/javascript">
    function toggle(id) {
        let block = document.getElementById(id);
        block.style.display = (block.style.display == 'none' ? 'block' : 'none');
    }

    function _da(id) {
        let dump = document.getElementById('argument-dumper');
        dump.style.display = 'block';

        dump.innerHTML = '<div class="close" onclick="toggle(\'argument-dumper\')"> &cross; close</div> '
            + '<div class="dump" style="display: block">'
            + document.getElementById('argument-' + id).innerHTML
            + '</div>';
    }
</script>
</body>
</html>
