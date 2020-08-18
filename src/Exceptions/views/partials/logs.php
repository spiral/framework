<div class="messages">
    <div class="container">
        <div class="title" onclick="toggle('log-events')">LOGS (<?= count($logEvents) ?>)</div>
        <div id="log-events" style="display: none;">
            <table>
                <?php
                /** @var \Spiral\Logger\Event\LogEvent $log */
                foreach ($logEvents as $log) {
                    echo sprintf(
                        '<tr><td class="channel">%s</td><td class="message">%s</td></tr>',
                        $log->getChannel(),
                        htmlspecialchars($log->getMessage())
                    );
                }
                ?>
            </table>
        </div>
    </div>
</div>
