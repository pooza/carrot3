<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * 抽象バリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Validator extends ParameterHolder {
	use BasicObject;
	protected $error;

	/**
	 * @access public
	 * @param iterable $params パラメータ配列
	 */
	public function __construct (?iterable $params = []) {
		$this->initialize($params);
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
				return ValidateManager::getInstance();
		}
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName ():string {
		return Utils::getShortClass($this);
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @return bool
	 */
	public function initialize (?iterable $params = []):bool {
		$this->setParameters($params);
		return true;
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象
	 * @return bool 妥当な値ならばTrue
	 * @abstract
	 */
	abstract public function execute ($value);

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError ():?string {
		return $this->error;
	}
}
