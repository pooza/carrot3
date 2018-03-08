<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage string
 */

namespace Carrot3;

/**
 * メッセージコンテナ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface MessageContainer {

	/**
	 * メッセージ文字列を返す
	 *
	 * @access public
	 * @return string メッセージ文字列
	 */
	public function getMessage ();
}
