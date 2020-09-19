<!-- email:image -->
<?php

/** @var \Symfony\Component\Mime\Email $_msg_ */
$_msg_->embedFromPath(inject('path'), 'img');
?>
<img src="cid:img" attr:aggregate/>
<!-- /email:image -->
