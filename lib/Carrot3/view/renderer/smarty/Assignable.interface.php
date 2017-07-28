<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty
 */

namespace Carrot3;

/**
 * アサイン可能
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface Assignable {

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign ();
}

