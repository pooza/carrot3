<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail
 */

namespace Carrot3;

/**
 * メール例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MailException extends NetException {

	/**
	 * ツイートするか
	 *
	 * @access public
	 * @return boolean ツイートするならTrue
	 */
	public function isTweetable () {
		return true;
	}
}
