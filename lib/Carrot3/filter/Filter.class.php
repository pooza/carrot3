<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * 抽象フィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Filter extends ParameterHolder {
	use BasicObject;
	static protected $executed;

	/**
	 * @access public
	 * @param iterable $params パラメータ配列
	 */
	public function __construct (iterable $params = []) {
		if (!self::$executed) {
			self::$executed = Tuple::create();
		}
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
			case 'action':
				return $this->controller->getAction();
		}
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ
	 * @return bool 初期化が成功すればTrue
	 */
	public function initialize (iterable $params = []) {
		$this->setParameters($params);
		return true;
	}

	/**
	 * フィルタ名を返す
	 *
	 * @access public
	 * @return string フィルタ名
	 */
	public function getName () {
		return Utils::getShortClass($this);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @return bool 終了ならばTrue
	 */
	abstract public function execute ();

	/**
	 * 実行できるか
	 *
	 * @access public
	 * @return bool 実行できるならTrue
	 */
	public function isExecutable ():bool {
		return (!$this->isExecuted() || $this->isRepeatable()) && !$this->isExcludedAction();
	}

	/**
	 * 二度目も実行するか
	 *
	 * @access public
	 * @return bool 二度目も実行するならTrue
	 */
	public function isRepeatable ():bool {
		return false;
	}

	/**
	 * 実行済みフラグを設定
	 *
	 * @access public
	 * @param bool $flag 実行されたならTrue
	 */
	public function setExecuted (bool $flag = true) {
		self::$executed[$this->getName()] = $flag;
	}

	/**
	 * 実行されたか？
	 *
	 * @access public
	 * @return bool 実行されたならTrue
	 */
	public function isExecuted ():bool {
		return !!self::$executed[$this->getName()];
	}

	/**
	 * 除外されたアクションか？
	 *
	 * @access public
	 * @return bool 除外されたアクションならTrue
	 */
	public function isExcludedAction ():bool {
		return Tuple::create($this['excluded_actions'])->isContain($this->action->getName());
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('フィルタ "%s"', Utils::getClass($this));
	}
}
