<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * Blink
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class BlinkUserAgent extends WebKitUserAgent {
	const DEFAULT_NAME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36';

	/**
	 * Google Chromeか？
	 *
	 * @access public
	 * @return bool Google ChromeならTrue
	 */
	public function isChrome ():bool {
		return true;
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return 'Chrome';
	}
}
