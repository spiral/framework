<?php

/**
 * Build email using extended DSL.
 *
 * @var \Symfony\Component\Mime\Email $_msg_
 */
$_msg_->subject(\Spiral\SendIt\Renderer\ViewRenderer::escapeSubject(inject('subject')));

if (injected('from')) {
    $_msg_->from(\Symfony\Component\Mime\Address::create(inject('from')));
}

/**
 * all the message settings, headers and attachments can be
 * directly defined in a context
 */
?><stack:collect name="partials"/>${context}<?php

ob_start(); ?>${html}<?php $_html_ = ob_get_clean();

if (!empty($_html_)) {
    $_msg_->html($_html_);
}

ob_start(); ?>${text}<?php $_text_ = ob_get_clean();

if (!empty($_text_)) {
    $_msg_->text($_text_);
}
?>
