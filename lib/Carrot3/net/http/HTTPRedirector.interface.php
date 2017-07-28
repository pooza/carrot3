<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http
 */

namespace Carrot3;

/**
 * リダイレクト対象
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface HTTPRedirector {

	/**
	 * リダイレクト対象
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL ();

	/**
	 * リダイレクト
	 *
	 * @access public
	 * @return string ビュー名
	 */
	public function redirect ();

	/**
	 * URLをクローンして返す
	 *
	 * @access public
	 * @return URL
	 */
	public function createURL ();
}

