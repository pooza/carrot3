#!/usr/local/bin/php
<?php
define('BS_ROOT_DIR', dirname(__DIR__));
require_once BS_ROOT_DIR . '/lib/Spyc.php';
require_once BS_ROOT_DIR . '/lib/Carrot3/crypt/cryptor/Cryptor.interface.php';
require_once BS_ROOT_DIR . '/lib/Carrot3/crypt/cryptor/OpenSSLCryptor.class.php';

function parse ($file) {
  return flatten('bs', Spyc::YAMLLoad(
    BS_ROOT_DIR . '/webapp/config/constant/' . $file . '.yaml'
  ));
}

function flatten ($prefix, $node) {
  $values = [];
  if (is_array($node)) {
    foreach ($node as $key => $value) {
      $values += flatten($prefix . '_' . $key, $value);
    }
  } else {
    $values[strtoupper($prefix)] = $node;
  }
  return $values;
}

foreach ([basename(BS_ROOT_DIR), 'application', 'carrot'] as $file) {
  foreach (parse($file) as $key => $value) {
    if (!defined($key)) {
      define($key, $value);
    }
  }
}

$source = $_SERVER['argv'][1];
$cryptor = new \Carrot3\OpenSSLCryptor;
$encrypted = base64_encode($cryptor->encrypt($source));
$decrypted = $cryptor->decrypt(base64_decode($encrypted));
echo json_encode([
  'source' => $source,
  'encrypted' => $encrypted,
  'method' => BS_CRYPT_METHOD,
  'verify' => (($source === $decrypted) ? 'OK' : 'NG'),
], JSON_PRETTY_PRINT) . "\n";
