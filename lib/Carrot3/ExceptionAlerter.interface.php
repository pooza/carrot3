<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * 例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface ExceptionAlerter {

	/**
	 * アラート
	 *
	 * @access public
	 * @param MessageContainer $message
	 */
	public function alert (MessageContainer $message);
}
