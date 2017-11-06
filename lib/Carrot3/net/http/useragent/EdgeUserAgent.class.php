<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * Edgeユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EdgeUserAgent extends WebKitUserAgent {
	const DEFAULT_NAME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393';

	/**
	 * Edgeか？
	 *
	 * @access public
	 * @return boolean Google ChromeならTrue
	 */
	public function isEdge () {
		return true;
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return 'Edge';
	}
}
