<div class="tags">
    <div class="container">
        <?php
        foreach ($tags as $tag => $value) {
            echo sprintf(
                '<div class="tag"><div class="name">%s</div><div class="value">%s</div></div>',
                $tag,
                htmlspecialchars($value)
            );
        }
        ?>
    </div>
</div>
