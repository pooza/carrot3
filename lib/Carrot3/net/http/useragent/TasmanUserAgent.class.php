<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * Tasmanユーザーエージェント
 *
 * Mac版InternetExplorer等。
 * Tasmanエンジンを搭載するのは5.xのみだが、便宜上、それ以前のバージョンも扱う。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TasmanUserAgent extends UserAgent {

	/**
	 * レガシー環境/旧機種か？
	 *
	 * @access public
	 * @return boolean レガシーならばTrue
	 */
	public function isLegacy () {
		return true;
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return 'MSIE [1-5]\\.[[:digit:]]+; Mac';
	}
}

