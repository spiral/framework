<div class="footer">
    <div class="date"><?= date('r') ?></div>
    <div class="elapsed time">
        <span>Elapsed:</span>
        <?= number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4) ?> seconds
    </div>
    <div class="elapsed memory">
        <span>Memory peak usage:</span> <?= number_format(memory_get_peak_usage() / 1024, 2) ?> Kb
    </div>
</div>