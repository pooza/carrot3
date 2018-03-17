<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net
 */

namespace Carrot3;

/**
 * ネットワーク例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class NetException extends Exception {

	/**
	 * アラートを送るか
	 *
	 * @access public
	 * @return bool アラートを送るならTrue
	 */
	public function isAlertable () {
		return true;
	}
}
