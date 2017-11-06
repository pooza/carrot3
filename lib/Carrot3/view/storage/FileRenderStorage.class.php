<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.storage
 */

namespace Carrot3;

/**
 * ファイルレンダーストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FileRenderStorage implements RenderStorage {
	private $directory;

	/**
	 * @access public
	 */
	public function __construct () {
		$this->directory = FileUtils::getDirectory('output');
	}

	/**
	 * キャッシュを返す
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return View キャッシュ
	 */
	public function getCache (Action $action) {
		if ($file = $this->directory->getEntry($action->digest())) {
			return Tuple::create((new PHPSerializer)->decode($file->getContents()));
		}
	}

	/**
	 * キャッシュを削除
	 *
	 * @access public
	 * @param Action $action アクション
	 */
	public function removeCache (Action $action) {
		if ($file = $this->directory->getEntry($action->digest())) {
			return $file->delete();
		}
	}

	/**
	 * レスポンスをキャッシュする
	 *
	 * @access public
	 * @param HTTPResponse $view キャッシュ対象
	 */
	public function cache (HTTPResponse $view) {
		$data = ['headers' => [], 'contents' => $view->getRenderer()->getContents()];
		foreach ($view->getHeaders() as $header) {
			if ($header->isVisible() && $header->isCacheable()) {
				$data['headers'][$header->getName()] = $header->getContents();
			}
		}
		$file = $this->directory->createEntry($view->getAction()->digest());
		$file->setContents((new PHPSerializer)->encode($data));
	}

	/**
	 * キャッシュを持っているか？
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return boolean キャッシュを持っていたらTrue
	 */
	public function hasCache (Action $action) {
		return !!$this->directory->getEntry($action->digest());
	}

	/**
	 * 全てのキャッシュをクリア
	 *
	 * @access public
	 */
	public function clear () {
		$this->directory->clear();
	}
}
