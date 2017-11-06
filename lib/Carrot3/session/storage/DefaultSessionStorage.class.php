<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session.storage
 */

namespace Carrot3;

/**
 * 規定セッションストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DefaultSessionStorage implements SessionStorage {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		ini_set('session.save_handler', 'files');
		ini_set('session.save_path', FileUtils::getPath('tmp'));
		return true;
	}
}
