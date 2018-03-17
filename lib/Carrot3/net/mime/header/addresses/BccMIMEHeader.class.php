<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header.addresses
 */

namespace Carrot3;

/**
 * BCCヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class BccMIMEHeader extends AddressesMIMEHeader {
	protected $name = 'Bcc';

	/**
	 * 可視か？
	 *
	 * @access public
	 * @return bool 可視ならばTrue
	 */
	public function isVisible () {
		return false;
	}
}
