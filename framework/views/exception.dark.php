<?php
/**
 * @var \Spiral\Components\Debug\Snapshot $exception
 */
use Spiral\Components\Tokenizer\Tokenizer;
use Spiral\Components\Debug\Debugger;
use Spiral\Components\Debug\Logger;

$tokenizer = Tokenizer::getInstance();

/**
 * Code highlighting styles.
 */
$tokenizer->setHighlightingStyles(array(
    'color: #C26230; font-weight: bold;' => array(
        T_STATIC, T_PUBLIC, T_PRIVATE, T_PROTECTED, T_CLASS,
        T_NEW, T_FINAL, T_ABSTRACT, T_IMPLEMENTS, T_CONST,
        T_ECHO, T_CASE, T_FUNCTION, T_GOTO, T_INCLUDE,
        T_INCLUDE_ONCE, T_REQUIRE, T_REQUIRE_ONCE, T_VAR,
        T_INSTANCEOF, T_INTERFACE, T_THROW, T_ARRAY,
        T_IF, T_ELSE, T_ELSEIF, T_TRY, T_CATCH, T_CLONE,
        T_WHILE, T_FOR, T_DO, T_UNSET, T_FOREACH, T_RETURN,
        T_EXIT
    ),
    'color: black; font: weight: bold;'  => array(
        T_OPEN_TAG, T_CLOSE_TAG, T_OPEN_TAG_WITH_ECHO
    ),
    'color: #BC9458;'                    => array(
        T_COMMENT, T_DOC_COMMENT
    ),
    'color: #A5C261;'                    => array(
        T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_DNUMBER, T_LNUMBER
    ),
    'color: #D0D0FF;'                    => array(
        T_VARIABLE
    )
));

/**
 * Variable dumping styles.
 */
Debugger::dumpingStyles(array(
    'maxLevel'  => 10,
    'container' => 'background-color: #232323;',
    'indent'    => '&middot;    ',
    'styles'    => array(
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
    )
));

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $exception->getMessage() ?></title>
    <script type="text/javascript">
        function toggleBlock(blockID) {
            var block = document.getElementById(blockID);
            block.style.display = (block.style.display == 'none' ? 'block' : 'none');
        }

        function showDump(id) {
            var dumper = document.getElementById('backtrace-dumper');
            dumper.style.display = 'block';
            dumper.innerHTML = '<div class="close" onclick="toggleBlock(\'backtrace-dumper\')">close</div> '
            + document.getElementById('dump-argument-' + id).innerHTML;
        }
    </script>
    <style>
        body {
            background-color: #232323;
        }

        div.backtrace.wrapper {
            font-family: Arial, Helvetica;
            font-size: 14px;
            padding-left: 10px;
            padding-right: 10px;
            margin: 20px;
            border: 1px solid gray;
            background-color: #454749;
        }

        div.backtrace div.exception {
            margin-top: 10px;
            margin-bottom: 10px;
            padding: 10px;
            padding-left: 5px;
            background-color: rgba(134, 0, 0, 0.92);
            font-weight: bold;
            color: #cccccc;
        }

        div.backtrace div.exception span {
            color: white;
        }

        div.backtrace div.tracing {
            margin-bottom: 10px;
            display: inline-block;
            width: 100%;
        }

        div.backtrace div.tracing div.code {
            border: 0;
            width: 70%;
            float: left;
            display: inline-block;
            overflow: hidden;
        }

        div.backtrace div.tracing div.functions {
            font-size: 80%;
            border: 0;
            width: 30%;
            float: right;
            display: inline-block;
            overflow: hidden;
        }

        div.backtrace div.tracing div.code div.container {
            padding: 5px;
            padding-bottom: 0px;
            background-color: #232323;
            margin-right: 10px;
            margin-bottom: 10px;
            overflow-x: auto;
        }

        div.backtrace div.tracing div.code div.container div.location {
            color: #A5C261;
        }

        div.backtrace div.tracing div.code div.container div {
            font-family: Monospace;
            display: block;
            color: #E6E1DC;
        }

        div.backtrace div.tracing div.code div.container div:hover {
            background-color: #2e2e2e;
        }

        div.backtrace div.tracing div.code div.container div.number {
            display: inline-block;
            width: 50px;
            color: #666;
        }

        div.backtrace div.tracing div.code div.container div.highlighted {
            background-color: #2e2e2e;
        }

        div.backtrace div.tracing div.functions div.container {
            padding: 5px;
            color: #E6E1DC;
            background-color: #232323;
        }

        div.backtrace div.tracing div.functions div.name {
            color: #BC9458;
        }

        div.backtrace div.tracing div.functions div.container.dump {
            padding: 5px;
            margin-top: 10px;
            background-color: #232323;
            overflow-x: auto;
            display: none;
        }

        div.backtrace div.tracing div.functions div.container.dump div.close {
            float: right;
            font-size: 12px;
            color: #E6E1DC;
            cursor: pointer;
        }

        div.backtrace div.tracing div.functions div.container.dump div.close:hover {
            text-decoration: underline;
        }

        div.backtrace div.tracing div.functions div.container div.function div.location {
            font-size: 10px;
            color: #E6E1DC;
            margin-bottom: 10px;
        }

        div.backtrace div.tracing div.functions div.container div.function span.arguments {
            color: #E6E1DC;
        }

        div.backtrace div.tracing div.functions div.container div.function span.arguments span.argument {
            cursor: pointer;
        }

        div.backtrace div.tracing div.functions div.container div.function span.arguments span.argument div.dump {
            display: none;
        }

        div.backtrace div.tracing div.functions div.container div.function span.arguments span.argument:hover span {
            text-decoration: underline;
        }

        div.backtrace div.environment div.container {
            margin-bottom: 10px;
        }

        div.backtrace div.environment div.title {
            padding: 10px;
            padding-left: 5px;
            background-color: #c87b11;
            font-weight: bold;
            color: black;
            cursor: pointer;
        }

        div.backtrace div.environment div.dump {
            padding: 5px;
            background-color: #232323;
            margin-top: 0;
            display: none;
            overflow-x: auto;
        }

        div.backtrace div.messages {
            margin-bottom: 10px;
        }

        div.backtrace div.messages div.title {
            padding: 10px;
            padding-left: 5px;
            background-color: #669933;
            font-weight: bold;
            color: white;
        }

        div.backtrace div.messages div.message {
            padding: 5px;
            color: #E6E1DC;
            background-color: #232323;
        }

        div.backtrace div.messages div.message:nth-child(2n) {
            background-color: #1e1e1e;
        }

        div.backtrace div.messages div.message div.container {
            display: inline-block;
            font-weight: bold;
            width: 300px;
            float: left;
        }

        div.backtrace div.messages div.message div.level {
            display: inline-block;
            font-weight: bold;
            color: #A5C261;
            width: 70px;
            float: left;
            text-transform: uppercase;
        }

        div.backtrace div.messages div.message.error {
            background-color: rgba(45, 0, 0, 0.92);
        }

        div.backtrace div.messages div.message div.content {
            margin-left: 200px;
            unicode-bidi: embed;
            white-space: pre;
        }

        div.backtrace div.messages div.message:hover {
            background-color: #1e1e1e;
        }

        div.backtrace div.footer {
            margin-bottom: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="backtrace wrapper">
    <div class="exception">
        <?php echo $exception->getClass() ?>:
        <span><?php echo $exception->getException()->getMessage() ?></span>
        in <span><?php echo $exception->getFile() ?></span> at <span>line <?php echo $exception->getLine() ?></span>
    </div>

    <div class="tracing">
        <div class="code">
            <div class="container">
                <div class="location"><?php echo $exception->getFile() ?> at
                    <strong>line <?php echo $exception->getLine() ?></strong></div>
                <pre><?php echo $tokenizer->highlightCode($exception->getFile(), $exception->getLine()) ?></pre>
            </div>
            <?php
            $tracing = $exception->getTrace();
            foreach ($tracing as $trace)
            {
                if (isset($trace['file']) && $trace['file'] != $exception->getFile() && $trace['line'] != $exception->getLine())
                {
                    ?>
                    <div class="container">
                        <div class="location"><?php echo $trace['file'] ?> at
                            <strong>line <?php echo $trace['line'] ?></strong></div>
                        <pre><?php echo $tokenizer->highlightCode($trace['file'], $trace['line']) ?></pre>
                    </div>
                <?php
                }
            }
            ?>
        </div>
        <div class="functions">
            <div class="container">
                <?php
                $tracing = array_reverse($tracing);
                $argumentID = 1;

                foreach ($tracing as $number => &$trace)
                {
                    if (!isset($trace['file']) && isset($tracing[$number - 1]['file']))
                    {
                        $trace['file'] = $tracing[$number - 1]['file'];
                        $trace['line'] = $tracing[$number - 1]['line'];
                    }

                    if (!isset($tracing[$number + 1]))
                    {
                        $trace['file'] = $exception->getFile();
                        $trace['line'] = $exception->getLine();
                    }

                    if (isset($trace['function']))
                    {
                        $function = '<strong>' . $trace['function'] . '</strong>';
                        if (isset($trace['type']))
                        {
                            $function = $trace['class'] . $trace['type'] . $function;
                        }

                        $arguments = array();

                        if (isset($trace['args']))
                        {
                            foreach ($trace['args'] as $argument)
                            {
                                $type = strtolower(gettype($argument));
                                $display = $type;

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
                                    $argument = 'null';
                                }

                                if (is_object($argument))
                                {
                                    $display = get_class($argument) . ' object';
                                }

                                $display = Debugger::getStyle($display, 'value', $type);
                                $display = '<span class="argument" onclick="showDump(' . $argumentID . ')">' . $display
                                    . '<div id="dump-argument-' . $argumentID . '" class="dump">' .
                                    $argument = Debugger::dump($argument, true) . '</div></span>';

                                $arguments[] = $display;
                                $argumentID++;
                            }
                        }
                        ?>
                        <div class="function">
                            <div class="name">
                                <?php echo $function ?> (<span
                                    class="arguments"><?php echo join(', ', $arguments) ?></span>)
                            </div>
                            <div class="location">
                                <?php echo $trace['file'] ?> at <strong>line <?php echo $trace['line'] ?></strong>
                            </div>
                        </div>
                    <?php
                    }
                }
                ?>
            </div>
            <div id="backtrace-dumper" class="container dump">
            </div>
        </div>
    </div>

    <div class="environment">
        <?php
        $variables = array(
            'GET'     => '_GET',
            'POST'    => '_POST',
            'COOKIES' => '_COOKIE',
            'SESSION' => '_SESSION',
            'SERVER'  => '_SERVER',
        );

        foreach ($variables as $name => $variable)
        {
            if (!empty($GLOBALS[$variable]))
            {
                if ($name == 'SERVER' && isset($_SERVER['PATH']) && is_string($_SERVER['PATH']))
                {
                    $_SERVER['PATH'] = explode(PATH_SEPARATOR, $_SERVER['PATH']);
                }

                //                if ($name == 'COOKIES')
                //                {
                //                    $GLOBALS[$variable] = \spiral\core\facades\Cookies::getAll();
                //                }

                ?>
                <div class="container">
                    <div class="title" onclick="toggleBlock('dump-<?php echo $name?>')"><?php echo $name?>
                        (<?php echo count($GLOBALS[$variable]) ?>)
                    </div>
                    <div class="dump" id="dump-<?php echo $name?>" style="display: none;">
                        <?php Debugger::dump($GLOBALS[$variable]) ?>
                    </div>
                </div>
            <?php
            }
        }
        ?>
    </div>

    <div class="messages">
        <div class="title">Messages</div>
        <?php
        foreach (Logger::logMessages() as $message)
        {
            ?>
            <div class="message <?php echo $message[2]?>">
                <div class="container"><?php echo $message[0]?></div>
                <div class="level"><?php echo $message[2]?></div>
                <div class="content"><?php echo $message[3]?></div>
            </div>
        <?php
        }
        ?>
    </div>

    <div class="footer">
        <?php echo date('r') ?>
        <br/>
        Elapsed: <b><?php echo number_format(microtime(true) - SPIRAL_INITIAL_TIME, 4) ?></b> seconds, Memory:
        <b><?php echo number_format(memory_get_peak_usage() / 1024, 1) ?></b> Kb
    </div>
</div>
</body>
</html>