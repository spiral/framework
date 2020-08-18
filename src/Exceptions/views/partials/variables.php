<div class="variables">
    <?php foreach ($variables as $name => $content) : ?>
        <div class="container">
            <div class="title" onclick="toggle('<?= md5($name) ?>-variables')">
                <?= strtoupper($name) ?>
            </div>
            <div class="dump" id="<?= md5($name) ?>-variables" style="display: none;">
                <?php
                if (!is_array($content)) {
                    echo is_scalar($value) ? htmlspecialchars($value) : json_encode($value, JSON_PRETTY_PRINT);
                } else { ?>
                    <table>
                        <?php
                        foreach ($content as $key => $value) {
                            // todo: values
                            echo sprintf(
                                '<tr><td class="name">%s</td><td>%s</td></tr>',
                                $key,
                                is_scalar($value) ? htmlspecialchars($value) : json_encode($value, JSON_PRETTY_PRINT)
                            );
                        }
                        ?>
                    </table>
                <?php } ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
