<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminUtility
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminUtilityModule;
use \Carrot3 as C;

class AdminUtilityModule extends C\Module {

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		return 'ユーティリティ';
	}
}

