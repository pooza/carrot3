#!/usr/local/bin/php
<?php
define('BS_ROOT_DIR', dirname(dirname(__FILE__)));
define('DOMAIN', basename(BS_ROOT_DIR));
require_once BS_ROOT_DIR . '/lib/Spyc.php';
require_once BS_ROOT_DIR . '/lib/carrot/crypt/cryptor/BSCryptor.interface.php';
require_once BS_ROOT_DIR . '/lib/carrot/crypt/cryptor/BSOpenSSLCryptor.class.php';

function parse ($file) {
  $path = BS_ROOT_DIR . '/webapp/config/constant/' . $file . '.yaml';
  return flatten('bs', Spyc::YAMLLoad($path));
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

foreach ([DOMAIN, 'application', 'carrot'] as $file) {
  foreach (parse($file) as $key => $value) {
    if (!defined($key)) {
      define($key, $value);
    }
  }
}

$source = $_SERVER['argv'][1];
$cryptor = new BSOpenSSLCryptor;
$encrypted = base64_encode($cryptor->encrypt($source));
$decrypted = $cryptor->decrypt(base64_decode($encrypted));
?>
source:    <?= $source ?> 
encrypted: <?= $encrypted ?> 
method:    <?= BS_CRYPT_METHOD ?> 
verify:    <?= ($source === $decrypted) ? 'OK' : 'NG' ?> 
