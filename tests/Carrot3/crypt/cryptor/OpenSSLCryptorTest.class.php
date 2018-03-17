<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class OpenSSLCryptorTest extends Test {
	public function execute () {
		$cryptor = new OpenSSLCryptor;
		$data = Utils::getUniqueID();
		$this->assert('encrypt', $encrypted = $cryptor->encrypt($data));
		$this->assert('decrypt', $decrypted = $cryptor->decrypt($encrypted));
		$this->assert('equal', $data === $decrypted);
	}
}
