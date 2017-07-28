<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Default
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DefaultModule;
use \Carrot3 as C;

class DefaultModule extends C\Module {

	/**
	 * タイトルを返す
	 *
	 * 固有のモジュールではないと考えられるので、タイトルは不要。
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		return null;
	}
}

