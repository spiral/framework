<!-- email:attach -->
<?php

/** @var \Symfony\Component\Mime\Email $_msg_ */
$_msg_->attachFromPath(inject('path'), inject('name', null), inject('mime', null));
?>
<!-- /email:attach -->
