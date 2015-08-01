<!DOCTYPE html>
<?php
/**
 * @var \Spiral\Debug\Snapshot          $snapshot
 * @var \Spiral\Core\ContainerInterface $container
 */

$highlighter = new \Spiral\Tokenizer\Hightligher($container->get(\Spiral\Tokenizer\TokenizerInterface::class), [
    'styles' => [
        'color: #C26230; font-weight: bold;' => [
            T_STATIC, T_PUBLIC, T_PRIVATE, T_PROTECTED, T_CLASS,
            T_NEW, T_FINAL, T_ABSTRACT, T_IMPLEMENTS, T_CONST,
            T_ECHO, T_CASE, T_FUNCTION, T_GOTO, T_INCLUDE,
            T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE, T_VAR,
            T_INSTANCEOF, T_INTERFACE, T_THROW, T_ARRAY,
            T_IF, T_ELSE, T_ELSEIF, T_TRY, T_CATCH, T_CLONE,
            T_WHILE, T_FOR, T_DO, T_UNSET, T_FOREACH, T_RETURN,
            T_EXIT, T_EXTENDS
        ],
        'color: black; font: weight: bold;'  => [
            T_OPEN_TAG, T_CLOSE_TAG, T_OPEN_TAG_WITH_ECHO
        ],
        'color: #BC9458;'                    => [
            T_COMMENT, T_DOC_COMMENT
        ],
        'color: #A5C261;'                    => [
            T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_DNUMBER, T_LNUMBER
        ],
        'color: #D0D0FF;'                    => [
            T_VARIABLE
        ]
    ]
]);

$dumper = new \Spiral\Debug\Dumper($container->get(\Spiral\Debug\Debugger::class), [
    'container' => '<pre style="background-color: #232323; font-family: Monospace;">{dump}</pre>',
    'styles'    => [
        'common'           => '#E6E1DC',
        'name'             => '#E6E1DC',
        'indent'           => 'gray',
        'indent-('         => '#E6E1DC',
        'indent-)'         => '#E6E1DC',
        'recursion'        => '#ff9900',
        'value-string'     => '#A5C261',
        'value-integer'    => '#A5C261',
        'value-double'     => '#A5C261',
        'value-boolean'    => '#C26230; font-weight: bold;',
        'type'             => '#E6E1DC',
        'type-object'      => '#E6E1DC',
        'type-array'       => '#C26230;',
        'type-null'        => '#C26230;',
        'type-resource'    => '#color: #C26230;',
        'access'           => '#666',
        'access-public'    => '#8dc17d',
        'access-private'   => '#c18c7d',
        'access-protected' => '#7d95c1'
    ]
]);

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= $snapshot->getMessage() ?></title>
    <style>
        body.spiral-exception {
            font-family: Helvetica, sans-serif;
            background-color: #e0e0e0;
            font-size: 14px;
            padding: 5px;
            color: #a1a1a1;
        }

        .spiral-exception .wrapper {
            padding: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .7);
            background-color: #3a3a3a;
        }

        .spiral-exception .wrapper strong {
            font-weight: bold;
        }

        .spiral-exception .wrapper i {
            font-style: italic;
        }

        .spiral-exception .dump {
            padding: 5px;
            background-color: #232323;
            margin-top: 0;
            display: none;
            overflow-x: auto;
        }

        .spiral-exception .wrapper .header {
            margin-bottom: 5px;
            background: #d34646;
            border: 2px solid #d34646;
            padding: 8px 13px 8px 18px;
            color: #fff;
            box-shadow: inset 0 0 8px rgba(0, 0, 0, .2);
        }

        .spiral-exception .wrapper .stacktrace {
            display: inline-block;
            width: 100%;
        }

        .spiral-exception .wrapper .stacktrace .trace {
            font-family: Monospace;
            float: left;
            width: 70%;
        }

        .spiral-exception .wrapper .stacktrace .trace .container {
            padding: 15px;
            background-color: #2e2e2e;
            margin-bottom: 5px;
            overflow-x: auto;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, .2);
        }

        .spiral-exception .wrapper .stacktrace .trace .container.no-trace {
            color: #6bbdff;

        }

        .spiral-exception .wrapper .stacktrace .trace .location {
            color: #6bbdff;
            margin-bottom: 5px;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div {
            white-space: pre;
            color: #E6E1DC;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div .number {
            display: inline-block;
            width: 50px;
            color: #757575;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div:hover {
            background-color: #404040;
        }

        .spiral-exception .wrapper .stacktrace .trace .lines div.highlighted {
            background-color: #404040;
        }

        .spiral-exception .wrapper .stacktrace .chain {
            width: 30%;
            float: right;
        }

        .spiral-exception .wrapper .stacktrace .chain .calls {
            padding: 10px 10px 10px 10px;
            margin-left: 5px;
            background-color: #2e2e2e;
            margin-bottom: 5px;
            overflow-x: auto;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, .2);
        }

        .spiral-exception .wrapper .stacktrace .chain .call .function {
            font-size: 11px;
            color: #6bbdff;
        }

        .spiral-exception .wrapper .stacktrace .chain .call .function .arguments span {
            cursor: pointer;
        }

        .spiral-exception .wrapper .stacktrace .chain .call .function .arguments span:hover {
            text-decoration: underline;
        }

        .spiral-exception .wrapper .stacktrace .chain .call .location {
            margin-bottom: 10px;
            font-size: 10px;
        }

        .spiral-exception .wrapper .stacktrace .chain .dumper {
            padding-left: 5px;
            display: none;
        }

        .spiral-exception .wrapper .stacktrace .chain .dumper .close {
            text-align: right;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            margin-bottom: 2px;
        }

        .spiral-exception .wrapper .environment .container {
            margin-bottom: 9px;
        }

        .spiral-exception .wrapper .environment .title, .spiral-exception .wrapper .messages .title {
            padding: 10px 10px 10px 5px;
            background-color: #e7c35e;
            font-weight: bold;
            color: #444;
            cursor: pointer;
        }

        .spiral-exception .wrapper .messages {
            margin-bottom: 5px;
        }

        .spiral-exception .wrapper .messages .data {
            display: none;
            background-color: #2e2e2e;
        }

        .spiral-exception .wrapper .messages .data .message {
            padding: 5px;
            color: #fff;
        }

        .spiral-exception .wrapper .messages .data .message:nth-child(2n) {
            background-color: #2e2e2e;
        }

        .spiral-exception .wrapper .messages .data .message .channel {
            display: inline-block;
            font-weight: bold;
            width: 200px;
            float: left;
        }

        .spiral-exception .wrapper .messages .data .message .level {
            display: inline-block;
            font-weight: bold;
            width: 70px;
            float: left;
            text-transform: uppercase;
        }

        .spiral-exception .wrapper .messages .data .message .body {
            margin-left: 200px;
            unicode-bidi: embed;
            white-space: pre;
        }

        .spiral-exception .wrapper .messages .data .message:hover {
            background-color: #1e1e1e;
        }

        .spiral-exception .wrapper .messages .data .message.warning,
        .spiral-exception .wrapper .messages .data .message.notice {
            background-color: #2e2e25;
        }

        .spiral-exception .wrapper .messages .data .message.error {
            background-color: #2e0e16;
        }

        .spiral-exception .wrapper .messages .data .message.critical,
        .spiral-exception .wrapper .messages .data .message.alert,
        .spiral-exception .wrapper .messages .data .message.emergency {
            background-color: #4c001f;
        }

        .spiral-exception .wrapper .footer {
            margin-top: 10px;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .spiral-exception .wrapper .footer .date {
            color: #fafafa;
        }
    </style>
    <script type="text/javascript">
        function toggle(id) {
            var block = document.getElementById(id);
            block.style.display = (block.style.display == 'none' ? 'block' : 'none');
        }
        function dumpArgument(id) {
            var dump = document.getElementById('argument-dumper');
            dump.style.display = 'block';
            dump.innerHTML = '<div class="close" onclick="toggle(\'argument-dumper\')"> &cross; close</div> '
            + '<div class="dump" style="display: block">'
            + document.getElementById('argument-' + id).innerHTML
            + '</div>';

            window.location.href = "#dumper";
        }
    </script>
</head>
<body class="spiral-exception">
<div class="wrapper">
    <div class="header">
        <?= $snapshot->getClass() ?>:
        <strong><?= $snapshot->getException()->getMessage() ?></strong>
        in <i><?= $snapshot->getFile() ?></i> at <strong>line <?= $snapshot->getLine() ?></strong>
    </div>

    <div class="stacktrace">
        <div class="trace">
            <div class="container">
                <div class="location">
                    <i><?= $snapshot->getFile() ?></i> at
                    <strong>line <?= $snapshot->getLine() ?></strong>
                </div>
                <div class="lines">
                    <?= $highlighter->highlight($snapshot->getFile(), $snapshot->getLine()) ?>
                </div>
            </div>
            <?php
            $stacktrace = $snapshot->getTrace();
            foreach ($stacktrace as $trace)
            {
                if (!isset($trace['file']))
                {
                    ?>
                    <div class="container no-trace" title="See execution chain">
                        <?= $trace['class'] . $trace['type'] . $trace['function'] ?>(...)
                    </div>
                    <?php
                    continue;
                }

                if ($trace['file'] == $snapshot->getFile() && $trace['line'] == $snapshot->getLine())
                {
                    //Duplicate
                    continue;
                }

                ?>
                <div class="container">
                    <div class="location">
                        <i><?= $trace['file'] ?></i> at <strong>line <?= $trace['line'] ?></strong>
                    </div>
                    <div class="lines">
                        <?= $highlighter->highlight($trace['file'], $trace['line']) ?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <div class="chain">
            <div class="calls">
                <?php
                $dumps = [];
                $stacktrace = array_reverse($stacktrace);
                foreach ($stacktrace as $index => $trace)
                {
                    if (!isset($trace['file']) && isset($tracing[$index - 1]['file']))
                    {
                        $trace['file'] = $tracing[$index - 1]['file'];
                        $trace['line'] = $tracing[$index - 1]['line'];
                    }

                    if (!isset($tracing[$index + 1]))
                    {
                        $trace['file'] = $snapshot->getFile();
                        $trace['line'] = $snapshot->getLine();
                    }

                    if (!isset($trace['function']))
                    {
                        continue;
                    }

                    $function = '<strong>' . $trace['function'] . '</strong>';
                    if (isset($trace['type']) && isset($trace['class']))
                    {
                        $reflection = new ReflectionClass($trace['class']);
                        $function = interpolate(
                            "<span title=\"{title}\">{class}</span>{type}{function}",
                            [
                                'title'    => $reflection->getName(),
                                'class'    => $reflection->getShortName(),
                                'type'     => $trace['type'],
                                'function' => $function
                            ]
                        );
                    }

                    $arguments = [];
                    if (isset($trace['args']))
                    {
                        foreach ($trace['args'] as $argument)
                        {
                            $display = $type = strtolower(gettype($argument));

                            if (is_numeric($argument))
                            {
                                $display = $argument;
                            }
                            elseif (is_bool($argument))
                            {
                                $display = $argument ? 'true' : 'false';
                            }
                            elseif (is_null($argument))
                            {
                                $display = 'null';
                            }

                            if (is_object($argument))
                            {
                                $reflection = new ReflectionClass($argument);
                                $display = interpolate(
                                    "<span title=\"{title}\">{class}</span>", [
                                        'title' => $reflection->getName(),
                                        'class' => $reflection->getShortName()
                                    ]
                                );
                            }

                            //Colorizing
                            $display = $dumper->style($display, 'value', $type);
                            if (!empty($dumpArguments) && !in_array($argument, $dumps))
                            {
                                $dumps[] = $dumper->dump($argument, \Spiral\Debug\Dumper::OUTPUT_RETURN);
                                $display = interpolate(
                                    "<span onclick=\"dumpArgument({dumpID})\">{display}</span>",
                                    [
                                        'display' => $display,
                                        'dumpID'  => count($dumps) - 1
                                    ]
                                );
                            }

                            $arguments[] = $display;
                        }
                    }

                    ?>
                    <div class="call">
                        <div class="function">
                            <?= $function ?> (<span class="arguments"><?= join(', ', $arguments) ?></span>)
                        </div>
                        <div class="location">
                            <i><?= $trace['file'] ?></i> at
                            <strong>line <?= $trace['line'] ?></strong>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <a name="dumper"></a>

            <div class="dumper" id="argument-dumper"></div>
        </div>
    </div>

    <div class="environment">
        <?php
        $variables = [
            'GET'     => '_GET',
            'POST'    => '_POST',
            'COOKIES' => '_COOKIE',
            'SESSION' => '_SESSION',
            'SERVER'  => '_SERVER',
        ];

        foreach ($variables as $name => $variable)
        {
            if (!empty($GLOBALS[$variable]))
            {
                if ($name == 'SERVER' && isset($_SERVER['PATH']) && is_string($_SERVER['PATH']))
                {
                    $_SERVER['PATH'] = explode(PATH_SEPARATOR, $_SERVER['PATH']);
                }

                ?>
                <div class="container">
                    <div class="title" onclick="toggle('environment-<?= $name ?>')">
                        <?= $name ?> (<?= number_format(count($GLOBALS[$variable])) ?>)
                    </div>
                    <div class="dump" id="environment-<?= $name ?>" style="display: none;">
                        <?= $dumper->dump($GLOBALS[$variable], \Spiral\Debug\Dumper::OUTPUT_RETURN) ?>
                    </div>
                </div>
            <?php
            }
        }
        ?>
    </div>

    <?php
    if (!empty($messages = \Spiral\Debug\Logger::logMessages()))
    {
        ?>
        <div class="messages">
            <div class="title" onclick="toggle('logger-messages')">
                Logger Messages (<?= number_format(count($messages)) ?>)
            </div>
            <div class="data" id="logger-messages">
                <?php
                foreach (\Spiral\Debug\Logger::logMessages() as $message)
                {
                    $channel = $message[\Spiral\Debug\Logger::MESSAGE_CHANNEL];
                    if (class_exists($channel))
                    {
                        $reflection = new ReflectionClass($channel);
                        $channel = $reflection->getShortName();
                    }
                    ?>
                    <div class="message <?= $message[\Spiral\Debug\Logger::MESSAGE_LEVEL] ?>">
                        <div class="channel" title=" <?= $message[\Spiral\Debug\Logger::MESSAGE_CHANNEL] ?>">
                            <?= $channel ?>
                        </div>
                        <div class="level"><?= $message[\Spiral\Debug\Logger::MESSAGE_LEVEL] ?></div>
                        <div class="body"><?= $message[\Spiral\Debug\Logger::MESSAGE_BODY] ?></div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="footer">
        <div class="date"><?= date('r') ?></div>
        <div class="elapsed time">
            <span>Elapsed:</span> <?= number_format(microtime(true) - SPIRAL_INITIAL_TIME, 3) ?> seconds
        </div>
        <div class="elapsed memory">
            <span>Memory:</span> <?= number_format(memory_get_peak_usage() / 1024, 2) ?> Kb
        </div>
    </div>
    <?php
    foreach ($dumps as $argumentID => $dump)
    {
        echo "<div id=\"argument-{$argumentID}\" style=\"display: none\">{$dump}</div>";
    }
    ?>
</div>
</body>
</html>