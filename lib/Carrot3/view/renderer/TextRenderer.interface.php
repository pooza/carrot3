<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer
 */

namespace Carrot3;

/**
 * テキストレンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface TextRenderer extends Renderer {

	/**
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding ();
}

