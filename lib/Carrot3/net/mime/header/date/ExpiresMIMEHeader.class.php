<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header.date
 */

namespace Carrot3;

/**
 * Expiresヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ExpiresMIMEHeader extends DateMIMEHeader {
	protected $name = 'Expires';

	/**
	 * キャッシュ可能か？
	 *
	 * @access public
	 * @return bool キャッシュ可能ならばTrue
	 */
	public function isCacheable ():bool {
		return false;
	}
}
