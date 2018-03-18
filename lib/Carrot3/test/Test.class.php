<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage test
 */

namespace Carrot3;

/**
 * 基底テスト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Test {
	use BasicObject;
	private $errors;
	private $name;

	/**
	 * @access public
	 */
	public function __construct () {
		$this->errors = Tuple::create();
	}

	/**
	 * @access public
	 * @param string $name プロパティ名
	 * @return mixed 各種オブジェクト
	 */
	public function __get (string $name) {
		switch ($name) {
			case 'controller':
			case 'request':
			case 'user':
			case 'loader':
				return Utils::executeMethod($name, 'getInstance');
			case 'manager':
				return TestManager::getInstance();
		}
	}

	/**
	 * テスト名を返す
	 *
	 * @access public
	 * @return string テスト名
	 */
	public function getName () {
		if (!$this->name) {
			if (mb_ereg('(.*)Test', Utils::getClass($this), $matches)) {
				$this->name = $matches[1];
			}
		}
		return $this->name;
	}

	/**
	 * テスト名にマッチするか？
	 *
	 * @access public
	 * @param string $name テスト名
	 * @param bool マッチするならTrue
	 */
	public function isMatched (string $name) {
		return StringUtils::isContain(
			StringUtils::toLower($name),
			StringUtils::toLower(Utils::getShortClass($this))
		);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @abstract
	 */
	abstract public function execute ();

	/**
	 * アサート
	 *
	 * @access public
	 * @param string $name アサーションの名前
	 * @param bool $assertion アサーションの内容
	 */
	public function assert (string $name, bool $assertion) {
		try {
			if (!$assertion) {
				return $this->setError($name);
			}
		} catch (\Exception $e) {
			return $this->setError($name);
		}
	}

	/**
	 * エラーを登録
	 *
	 * @access public
	 * @param string $name アサーションの名前
	 * @param string $message エラーメッセージ
	 */
	public function setError (string $name, $message = null) {
		$this->errors[] = $name;
		$message = new StringFormat('  %s NG!!!');
		$message[] = $name;
		$this->manager->put($message);
	}

	/**
	 * 全てのエラーを返す
	 *
	 * @access public
	 * @return Tuple 全てのエラー
	 */
	public function getErrors () {
		return $this->errors;
	}
}
