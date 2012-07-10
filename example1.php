<?php
include 'cseg.php';

$fp = fopen('sample.txt', 'r');
$text = fread($fp, filesize('sample.txt'));
fclose($fp);

$start = microtime(true);
$cseg = new CSeg();
$cseg->loadDicts(array('dicts/default.dict.txt'));
$words = $cseg->segment($text);
$end = microtime(true);

$consume = round(1000 * ($end - $start), 2);

echo 'Time consume: ', $consume, "ms\n";
echo implode('/', $words);
