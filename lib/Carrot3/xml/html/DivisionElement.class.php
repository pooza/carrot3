<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html
 */

namespace Carrot3;

/**
 * div要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DivisionElement extends HTMLElement {

	/**
	 * タグ名を返す
	 *
	 * @access public
	 * @return string タグ名
	 */
	public function getTag () {
		return 'div';
	}

	/**
	 * div要素のラッパーを返す
	 *
	 * @access protected
	 * @return DivisionElement ラッパー要素
	 */
	protected function createWrapper () {
		return $this;
	}
}
