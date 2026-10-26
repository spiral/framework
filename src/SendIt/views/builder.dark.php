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

\ob_start(); ?>${html}<?php $_html_ = \ob_get_clean();

// Strip HTML comments. Kept: Outlook conditional comments (`<!--[if mso]>`, `<!--[if !mso]><!-->`,
// `<!--[if !mso]><!-- -->`, `<!--<![endif]-->`) and raw-text elements, where `<!--` is content, not a comment.
// On a PCRE error (backtrack limit on a huge comment) the HTML is sent as is rather than dropped.
$_html_ = \preg_replace(
    '~<(style|script|textarea|title)\b[^>]*+>(?>[^<]++|<(?!/\1\s*>))*+</\1\s*>(*SKIP)(*FAIL)'
    . '|(?<!\]>)<!--(?!\[if\b|<!|>)(?>[^-]++|-(?!->))*+-->~is',
    '',
    $_html_,
) ?? $_html_;

if (!empty($_html_)) {
    $_msg_->html($_html_);
}

\ob_start(); ?>${text}<?php $_text_ = \ob_get_clean();

if (!empty($_text_)) {
    $_msg_->text($_text_);
}
?>
