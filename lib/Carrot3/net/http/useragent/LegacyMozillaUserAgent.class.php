<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * レガシーMozillaユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LegacyMozillaUserAgent extends UserAgent {

	/**
	 * レガシー環境/旧機種か？
	 *
	 * @access public
	 * @return bool レガシーならばTrue
	 */
	public function isLegacy ():bool {
		return true;
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return '^Mozilla/[1-4]\\..*\((Mac|Win|X11)';
	}
}
