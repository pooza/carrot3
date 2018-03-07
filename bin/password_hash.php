#!/usr/local/bin/php
<?php
$seconds = 0.05;
$source = $_SERVER['argv'][1];
$cost = 7;
do {
  $cost ++;
  $start = microtime(true);
  $hash = password_hash($source, PASSWORD_DEFAULT, ['cost' => $cost]);
  $end = microtime(true);
} while (($end - $start) < $seconds);

$info = password_get_info($hash);
echo json_encode([
  'source' => $source,
  'hash' => $hash,
  'algo' => $info['algoName'],
  'cost' => $info['options']['cost'],
  'verify' => (password_verify($source, $hash) ? 'OK' : 'NG'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
