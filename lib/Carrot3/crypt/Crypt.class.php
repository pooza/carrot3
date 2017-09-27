<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage crypt
 */

namespace Carrot3;

/**
 * 暗号化
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Crypt {
	use Singleton, BasicObject;
	private $engine;

	/**
	 * 暗号化器を返す
	 *
	 * @access public
	 * @return Cryptor 暗号化器
	 */
	public function getEngine () {
		if (!$this->engine) {
			$this->engine = $this->loader->createObject(BS_CRYPT_ENGINE . 'Cryptor');
		}
		return $this->engine;
	}

	/**
	 * 暗号化された文字列を返す
	 *
	 * @access public
	 * @param string $value 対象文字列
	 * @return string 暗号化された文字列
	 */
	public function encrypt ($value) {
		$value = $this->getEngine()->encrypt($value);
		$value = MIMEUtils::encodeBase64($value);
		return $value;
	}

	/**
	 * 複号化された文字列を返す
	 *
	 * @access public
	 * @param string $value 対象文字列
	 * @return string 複号化された文字列
	 */
	public function decrypt ($value) {
		$value = MIMEUtils::decodeBase64($value);
		$value = $this->getEngine()->decrypt($value);
		$value = trim($value);
		return $value;
	}

	/**
	 * パスワード認証
	 *
	 * @access public
	 * @param string $password 正規文字列
	 * @param string $challenge 認証対象
	 * @return boolean 一致するならTrue
	 */
	public function auth ($password, $challenge) {
		return password_verify($challenge, $password);
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @param mixed $value 対象文字列又はその配列
	 * @param string $method ダイジェスト方法
	 * @return string ダイジェスト文字列
	 * @static
	 */
	static public function digest ($value, $method = BS_CRYPT_DIGEST_METHOD) {
		if (!extension_loaded('hash')) {
			throw new CryptException('hashモジュールがロードされていません。');
		}
		if (!in_array($method, hash_algos())) {
			$message = new StringFormat('ハッシュ関数 "%s"は正しくありません。');
			$message[] = $method;
			throw new CryptException($message);
		}
		if (is_array($value) || ($value instanceof ParameterHolder)) {
			$value = Tuple::create($value)->join("\n", "\t");
		}
		return hash($method, $value . BS_CRYPT_DIGEST_SALT);
	}
}

