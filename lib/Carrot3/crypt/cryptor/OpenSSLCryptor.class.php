<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage crypt.cryptor
 */

namespace Carrot3;

/**
 * OpenSSL暗号
 *
 * @link https://blog.ohgaki.net/encrypt-decrypt-using-openssl
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class OpenSSLCryptor implements Cryptor {

	/**
	 * @access public
	 */
	public function __construct () {
		if (!extension_loaded('openssl')) {
			throw new CryptException('opensslモジュールがロードされていません。');
		}
		if (!extension_loaded('hash')) {
			throw new CryptException('hashモジュールがロードされていません。');
		}
	}

	/**
	 * 暗号化された文字列を返す
	 *
	 * @access public
	 * @param string $data 対象文字列
	 * @return string 暗号化された文字列
	 */
	public function encrypt ($data) {
		$salt = openssl_random_pseudo_bytes(16);

		$salted = '';
		$dx = '';

		// Salt the key(32) and iv(16) = 48
		while (strlen($salted) < 48) {
			$dx = hash('sha256', $dx . BS_CRYPT_PASSWORD . $salt, true);
			$salted .= $dx;
		}
		$key = substr($salted, 0, 32);
		$iv = substr($salted, 32,16);

		return $salt . openssl_encrypt($data, BS_CRYPT_METHOD, $key, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * 複号化された文字列を返す
	 *
	 * @access public
	 * @param string $data 対象文字列
	 * @return string 複号化された文字列
	 */
	public function decrypt ($data) {
		$salt = substr($data, 0, 16);
		$ct = substr($data, 16);

		$rounds = 3; // depends on key length
		$data00 = BS_CRYPT_PASSWORD . $salt;
		$hash = [];
		$hash[0] = hash('sha256', $data00, true);
		$result = $hash[0];
		for ($i = 1 ; $i < $rounds ; $i ++) {
			$hash[$i] = hash('sha256', $hash[$i - 1] . $data00, true);
			$result .= $hash[$i];
		}
		$key = substr($result, 0, 32);
		$iv = substr($result, 32,16);

		return openssl_decrypt($ct, BS_CRYPT_METHOD, $key, OPENSSL_RAW_DATA, $iv);
	}
}
