<extends:layout path="spiral:http/layouts/error" code="404" title="[[This page cannot be found]]"/>

<define:message>
    <?php
    /**
     * @var \Psr\Http\Message\ServerRequestInterface $request
     */
    $uri = $request->getUri();
    ?>
    [[The requested URL]] <a href="<?= $uri ?>"><?= $uri->getPath() ?></a> [[was not found on this server.]]
</define:message>
