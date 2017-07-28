<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminMemcache
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminMemcacheModule;
use \Carrot3 as C;

class AdminMemcacheModule extends C\Module {

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		return 'Memcache管理モジュール';
	}

	/**
	 * メニューでのタイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getMenuTitle () {
		return 'Memcache';
	}
}

