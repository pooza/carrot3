<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ファイル例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FileException extends Exception {

	/**
	 * アラートを送るか
	 *
	 * @access public
	 * @return boolean アラートを送るならTrue
	 */
	public function isAlertable () {
		return true;
	}
}
