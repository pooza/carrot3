<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage log
 */

namespace Carrot3;

/**
 * ログ例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LogException extends Exception {

	/**
	 * ログを書き込むか
	 *
	 * @access public
	 * @return boolean ログを書き込むならTrue
	 */
	public function isLoggable () {
		return false;
	}
}

