<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session
 */

namespace Carrot3;

/**
 * コンソール環境用セッションハンドラ
 *
 * セッション機能が必要な状況がない為、現状は単なるモック。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConsoleSessionHandler extends SessionHandler {

	/**
	 * @access public
	 */
	public function __construct () {
	}
}

