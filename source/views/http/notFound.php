<extends:http.layouts.error code="404" title="[[This page cannot be found]]"/>

<block:message>
    <?php
    /**
     * @var \Spiral\Http\HttpCore $html
     */
    $uri = $html->getRequest()->getUri();
    ?>
    [[The requested URL]] <a href="<?= $uri ?>"><?= $uri ?></a> [[was not found on this server.]]
</block:message>
