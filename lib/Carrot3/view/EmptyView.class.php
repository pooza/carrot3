<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view
 */

namespace Carrot3;

/**
 * レンダラーを持たないビュー
 *
 * アクションがView::NONEを返したとき、HEADリクエストされたとき等に使用。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EmptyView extends View {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return bool 初期化が成功すればTrue
	 */
	public function initialize () {
		return true;
	}

	/**
	 * レンダリング
	 *
	 * ヘッダの送信のみ。
	 *
	 * @access public
	 */
	public function render () {
		$this->putHeaders();
	}
}
