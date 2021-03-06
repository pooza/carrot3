<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session.storage
 */

namespace Carrot3;

/**
 * ファイルセッションストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FileSessionStorage implements SessionStorage {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return bool
	 */
	public function initialize ():bool {
		ini_set('session.save_handler', 'files');
		ini_set('session.save_path', FileUtils::getPath('tmp'));
		return true;
	}
}
