<?php class __StemplerView__f16512bfd01f751c0bc267994cadd546c0ad17dd4377e28099d577d6351f8d0a extends \Spiral\Stempler\StemplerView {
            public function render(array $data=[]): string {
                ob_start();
                $__outputLevel__ = ob_get_level();

                try {
                    Spiral\Core\ContainerScope::runScope($this->container, function () use ($data) {
                        extract($data, EXTR_OVERWRITE);
                        ?><?php

/**
 * Build email using extended DSL.
 *
 * @var \Symfony\Component\Mime\Email $_msg_
 */
$_msg_->subject(\Spiral\SendIt\Renderer\ViewRenderer::escapeSubject('Demo Email'));

if (injected('from')) {
    $_msg_->from(\Symfony\Component\Mime\Address::fromString(inject('from')));
}

/**
 * all the message settings, headers and attachments can be
 * directly defined in a context
 */
?>


<!-- email:attach -->
<?php

/** @var \Symfony\Component\Mime\Email $_msg_ */
$_msg_->attachFromPath(directory('root') . 'example.txt', 'bootstrap.txt', inject('mime', null));
?>
<!-- /email:attach -->



<?php

ob_start(); ?>
    <p>Hello, <?php echo htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'); ?>!</p>
<?php $_html_ = ob_get_clean();

if (!empty($_html_)) {
    $_msg_->html($_html_);
}

ob_start(); ?><?php $_text_ = ob_get_clean();

if (!empty($_text_)) {
    $_msg_->text($_text_);
}
?>
<?php
                    });
                } catch (\Throwable $e) {
                    while (ob_get_level() >= $__outputLevel__) { ob_end_clean(); }
                    throw $this->mapException(8, $e, $data);
                } finally {
                    while (ob_get_level() > $__outputLevel__) { ob_end_clean(); }
                }

                return ob_get_clean();
            }
        }