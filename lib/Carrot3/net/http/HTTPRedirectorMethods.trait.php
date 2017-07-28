<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http
 */

namespace Carrot3;

/**
 * リダイレクト対象
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait HTTPRedirectorMethods {

	/**
	 * リダイレクト
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function redirect () {
		return $this->getURL()->redirect();
	}

	/**
	 * URLをクローンして返す
	 *
	 * @access public
	 * @return URL
	 */
	public function createURL () {
		return clone $this->getURL();
	}
}

