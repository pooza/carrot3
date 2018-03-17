<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.storage
 */

namespace Carrot3;

/**
 * レンダーマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface RenderStorage {

	/**
	 * キャッシュを返す
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return View キャッシュ
	 */
	public function getCache (Action $action);

	/**
	 * キャッシュを削除
	 *
	 * @access public
	 * @param Action $action アクション
	 */
	public function removeCache (Action $action);

	/**
	 * レスポンスをキャッシュする
	 *
	 * @access public
	 * @param HTTPResponse $view キャッシュ対象
	 */
	public function cache (HTTPResponse $view);

	/**
	 * キャッシュを持っているか？
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return bool キャッシュを持っていたらTrue
	 */
	public function hasCache (Action $action);

	/**
	 * 全てのキャッシュをクリア
	 *
	 * @access public
	 */
	public function clear ();
}
