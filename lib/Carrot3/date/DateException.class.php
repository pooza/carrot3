<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage date
 */

namespace Carrot3;

/**
 * 日付例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DateException extends Exception {

	/**
	 * ログを書き込むか
	 *
	 * @access public
	 * @return bool ログを書き込むならTrue
	 */
	public function isLoggable ():bool {
		return false;
	}
}
