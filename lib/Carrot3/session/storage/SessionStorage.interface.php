<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session.storage
 */

namespace Carrot3;

/**
 * セッションストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface SessionStorage {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return bool
	 */
	public function initialize ():bool;
}
