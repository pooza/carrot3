<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.parser
 */

namespace Carrot3;

/**
 * 設定パーサー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface ConfigParser extends TextRenderer {

	/**
	 * 変換前の設定内容を設定
	 *
	 * @access public
	 * @param string $contents 設定内容
	 */
	public function setContents ($contents);

	/**
	 * 変換後の設定内容を返す
	 *
	 * @access public
	 * @return mixed[] 設定内容
	 */
	public function getResult ();
}

