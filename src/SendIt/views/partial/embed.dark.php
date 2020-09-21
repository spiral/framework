<!-- email:embed -->
<?php

/** @var \Symfony\Component\Mime\Email $_msg_ */
$_msg_->embedFromPath(inject('path'), inject('name', null), inject('mime', null));
?>
<!-- /email:embed -->
