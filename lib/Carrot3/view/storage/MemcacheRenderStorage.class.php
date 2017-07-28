<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.storage
 */

namespace Carrot3;

/**
 * Memcacheレンダーストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MemcacheRenderStorage implements RenderStorage {
	private $memcache;

	/**
	 * @access public
	 */
	public function __construct () {
		$this->memcache = MemcacheManager::getInstance()->getServer('render');
	}

	/**
	 * キャッシュを返す
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return View キャッシュ
	 */
	public function getCache (Action $action) {
		if ($data = $this->memcache[$action->digest()]) {
			return Tuple::create((new PHPSerializer)->decode($data));
		}
	}

	/**
	 * キャッシュを削除
	 *
	 * @access public
	 * @param Action $action アクション
	 */
	public function removeCache (Action $action) {
		$this->memcache->delete($action->digest());
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
		$this->memcache[$view->getAction()->digest()] = (new PHPSerializer)->encode($data);
	}

	/**
	 * キャッシュを持っているか？
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return boolean キャッシュを持っていたらTrue
	 */
	public function hasCache (Action $action) {
		return !!$this->memcache[$action->digest()];
	}

	/**
	 * 全てのキャッシュをクリア
	 *
	 * @access public
	 */
	public function clear () {
		if (!$this->memcache->getAttribute('error')) {
			$this->memcache->clear();
		}
	}
}

