<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * シングルトン
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait Singleton {
	static private $instance;

	/**
	 * @access protected
	 */
	protected function __construct () {
	}

	/**
	 * シングルトンインスタンスを返す
	 *
	 * @access public
	 * @return Request インスタンス
	 * @static
	 */
	static public function getInstance () {
		if (!self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * @access public
	 */
	public function __clone () {
		throw new \RuntimeException(__CLASS__ . 'はコピーできません。');
	}
}
