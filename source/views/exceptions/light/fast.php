<!DOCTYPE html>
<?php
/**
 * @see http://ppig.org/sites/default/files/2015-PPIG-26th-Sarkar.pdf
 * @var Throwable $exception
 */
$dumper = new \Spiral\Debug\Dumper(10, $styler = new \Spiral\Debug\Dumper\Style());
$dumps = [];

/**
 * Format arguments and create data dumps.
 *
 * @param array $arguments
 * @return array
 */
$argumenter = function (array $arguments) use ($dumper, $styler, &$dumps) {
    $result = [];
    foreach ($arguments as $argument) {
        $display = $type = strtolower(gettype($argument));

        if (is_numeric($argument)) {
            $result[] = $styler->apply($argument, 'value', $type);
            continue;
        } elseif (is_bool($argument)) {
            $result[] = $styler->apply($argument ? 'true' : 'false', 'value', $type);
            continue;
        } elseif (is_null($argument)) {
            $result[] = $styler->apply('null', 'value', $type);
            continue;
        }

        if (is_object($argument)) {
            $reflection = new ReflectionClass($argument);
            $display = \Spiral\interpolate(
                "<span title=\"{title}\">{class}</span>", [
                    'title' => $reflection->getName(),
                    'class' => $reflection->getShortName()
                ]
            );
        }

        //Colorizing
        $display = $styler->apply($display, 'value', $type);
        $display = \Spiral\interpolate("<span>{display}</span>", compact('display'));

        $result[] = $display;
    }

    return $result;
};

$highlightQuery = function (string $query) {
    if(class_exists('SqlFormatter')) {
        \SqlFormatter::$pre_attributes = '';

        //Cutting container
        return trim(substr(\SqlFormatter::highlight($query), 6, -6));
    }

    return $query;
}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>
        <?= \Spiral\Support\ExceptionHelper::createMessage($exception) ?>
    </title>
    <?php include 'style.php'; ?>
    <script type="text/javascript">
        function toggle(id) {
            var block = document.getElementById(id);
            block.style.display = (block.style.display == 'none' ? 'block' : 'none');
        }
    </script>
</head>
<body class="spiral-exception">
<a name="dump-top"></a>

<div class="wrapper">
    <div class="header">
        <?= get_class($exception) ?>:
        <strong><?= $exception->getMessage() ?></strong>
        in&nbsp;<i><?= $exception->getFile() ?></i>&nbsp;at&nbsp;<strong>line&nbsp;<?= $exception->getLine() ?></strong>
        <?php
        $previous = $exception->getPrevious();
        while($previous instanceof Throwable) {
            ?><div class="previous">
            &bull; caused by <?= get_class($previous) ?>:
            <strong><?= $previous->getMessage() ?></strong>
            in&nbsp;<i><?= $previous->getFile() ?></i>&nbsp;at&nbsp;<strong>line&nbsp;<?= $previous->getLine() ?></strong>
            </div>
            <?php
            $previous = $previous->getPrevious();
        }
        ?>
    </div>

    <?php if($exception instanceof \Spiral\Database\Exceptions\QueryExceptionInterface) {?>
        <div class="query"><?= $highlightQuery($exception->getQuery()) ?></div>
    <?php } ?>

    <div class="stacktrace">
        <div class="trace">
            <?php
            $stacktrace = $exception->getTrace();

            //Let's let's clarify exception location
            $header = [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ] + $stacktrace[0];

            if ($stacktrace[0] != $header) {
                array_unshift($stacktrace, $header);
            }

            foreach ($stacktrace as $trace) {

                $arguments = [];
                if (isset($trace['args'])) {
                    $arguments = $argumenter($trace['args']);
                }

                $function = '<strong>' . $trace['function'] . '</strong>';
                if (isset($trace['type']) && isset($trace['class'])) {
                    $reflection = new ReflectionClass($trace['class']);
                    $function = \Spiral\interpolate(
                        "<span title=\"{title}\">{class}</span>{type}{function}",
                        [
                            'title'    => $reflection->getName(),
                            'class'    => $reflection->getShortName(),
                            'type'     => $trace['type'],
                            'function' => $function
                        ]
                    );
                }

                if (!isset($trace['file'])) {
                    ?>
                    <div class="container no-trace">
                        <?= $function ?>(<span class="arguments"><?= join(
                                ', ',
                                $arguments
                            ) ?></span>)
                    </div>
                    <?php
                    continue;
                }

                ?>
                <div class="container">
                    <div class="location">
                        <?= $function ?>(<span class="arguments"><?= join(
                                ', ',
                                $arguments
                            ) ?></span>)<br/>
                        <em>
                            In&nbsp;<?= $trace['file'] ?>&nbsp;at&nbsp;<strong>line <?= $trace['line'] ?></strong>
                        </em>
                    </div>
                    <div class="lines">
                        <?= \Spiral\Support\ExceptionHelper::highlightSource(
                            $trace['file'],
                            $trace['line']
                        ) ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="chain">
            <div class="calls">
                <?php
                foreach ($stacktrace as $index => $trace) {
                    if (empty($trace['file']) && isset($stacktrace[$index - 1]['file'])) {
                        $trace['file'] = $stacktrace[$index - 1]['file'];
                        $trace['line'] = $stacktrace[$index - 1]['line'];
                    }

                    if (!isset($stacktrace[$index + 1])) {
                        $trace['file'] = $exception->getFile();
                        $trace['line'] = $exception->getLine();
                    }

                    if (!isset($trace['function']) || !isset($trace['file'])) {
                        continue;
                    }

                    $function = '<strong>' . $trace['function'] . '</strong>';
                    if (isset($trace['type']) && isset($trace['class'])) {
                        $reflection = new ReflectionClass($trace['class']);
                        $function = \Spiral\interpolate(
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
                    if (isset($trace['args'])) {
                        $arguments = $argumenter($trace['args']);
                    }

                    ?>
                    <div class="call">
                        <div class="function">
                            <?= $function ?>(<span class="arguments"><?= join(
                                    ', ',
                                    $arguments
                                ) ?></span>)
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

        foreach ($variables as $name => $variable) {
            if (!empty($GLOBALS[$variable])) {
                if ($name == 'SERVER' && isset($_SERVER['PATH']) && is_string($_SERVER['PATH'])) {
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
    <div class="footer">
        <div class="date"><?= date('r') ?></div>
        <div class="elapsed time">
            <?php
            if (!defined('SPIRAL_INITIAL_TIME')) {
                define(
                'SPIRAL_INITIAL_TIME',
                isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : 0
                );
            }
            ?>
            <span>Elapsed:</span>
            <?= number_format(microtime(true) - SPIRAL_INITIAL_TIME, 3) ?> seconds
        </div>
        <div class="elapsed memory">
            <span>Memory peak usage:</span> <?= number_format(memory_get_peak_usage() / 1024, 2) ?> Kb
        </div>
    </div>
    <?php
    foreach ($dumps as $argumentID => $dump) {
        echo "<div id=\"argument-{$argumentID}\" style=\"display: none\">{$dump}</div>";
    }
    ?>
</div>
</body>
</html>