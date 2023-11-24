<?php

function join_three_words($word1, $word2, $word3): string
{
    return $word1 . ' ' . $word2 . ' ' . $word3;
}

$a = 'Hello';
$b = 'world';

$c = 'John';
$d = 'Doe';

$e = 'John';
$f = 'Doe';

echo \sprintf(
    '%s %s',
    $a,
    join_three_words($b, $c, join_three_words($d, $e, $f))
); // Hello world John Doe John Doe
