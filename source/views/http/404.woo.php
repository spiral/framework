<layout:extends path="spiral:http/layouts/error" code="404" title="[[This page cannot be found]]"/>

<block:message>
    <?php
    /**
     * @var \Psr\Http\Message\ServerRequestInterface $request
     */
    $uri = $request->getUri();
    ?>
    [[The requested URL]] <a href="<?= $uri ?>"><?= $uri ?></a> [[was not found on this server.]]
</block:message>
