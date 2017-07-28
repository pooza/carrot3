<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage memcache
 */

namespace Carrot3;

/**
 * memcache例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MemcacheException extends Exception {

	/**
	 * メールを送るか
	 *
	 * @access public
	 * @return boolean メールを送るならTrue
	 */
	public function isMailable () {
		return true;
	}

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

