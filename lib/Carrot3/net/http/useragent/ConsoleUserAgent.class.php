<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * CLI環境用 ダミーユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConsoleUserAgent extends UserAgent {

	/**
	 * ビューを初期化
	 *
	 * @access public
	 * @param SmartyView 対象ビュー
	 * @return bool 成功時にTrue
	 */
	public function initializeView (SmartyView $view) {
		$view->getRenderer()->setUserAgent($this);
		$view->setAttributes($this->request->getAttributes());
		$view->setAttribute('module', $view->getModule());
		$view->setAttribute('action', $view->getAction());
		$view->setAttribute('errors', $this->request->getErrors());
		$view->setAttribute('params', $this->request->getParameters());
		$view->setAttribute('credentials', $this->user->getCredentials());
		return true;
	}

	/**
	 * セッションハンドラを生成して返す
	 *
	 * @access public
	 * @return SessionHandler
	 */
	public function createSession () {
		return new ConsoleSessionHandler;
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return '^Console$';
	}
}
