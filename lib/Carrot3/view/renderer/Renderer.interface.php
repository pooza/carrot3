<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer
 */

namespace Carrot3;

/**
 * レンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface Renderer {

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents ():string;

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return int サイズ
	 */
	public function getSize ();

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType ();

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ();

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError ();
}
