<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view
 */

namespace Carrot3;

/**
 * レンダーマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RenderManager {
	use Singleton, BasicObject;
	private $storage;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->storage = $this->loader->createObject(BS_RENDER_STORAGE . 'RenderStorage');
	}

	/**
	 * キャッシュを返す
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return View キャッシュ
	 */
	public function getCache (Action $action) {
		if ($action->isCacheable() && ($data = $this->storage->getCache($action))) {
			if (StringUtils::isBlank($data['contents'])) {
				$this->removeCache($action);
				return;
			}

			$view = new View($action, 'Success');
			$view->setRenderer(new RawRenderer);
			$view->getRenderer()->setContents($data['contents']);

			foreach ($data['headers'] as $key => $value) {
				$view->setHeader($key, $value);
			}
			if ($header = $view->getHeader('content-type')) {
				$view->getRenderer()->setType($header->getContents());
			}
			return $view;
		}
	}

	/**
	 * レスポンスをキャッシュする
	 *
	 * @access public
	 * @param HTTPResponse $view キャッシュ対象
	 */
	public function cache (HTTPResponse $view) {
		if ($view->getAction()->isCacheable()) {
			if ($view->getNameSuffix() == View::ERROR) {
				return;
			}
			if (StringUtils::isBlank($view->getRenderer()->getContents())) {
				return;
			}
			$this->storage->cache($view);
		}
	}

	/**
	 * キャッシュを削除
	 *
	 * @access public
	 * @param Action $action アクション
	 */
	public function removeCache (Action $action) {
		$this->storage->removeCache($action);
	}

	/**
	 * キャッシュを持っているか？
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return bool キャッシュを持っていたらTrue
	 */
	public function hasCache (Action $action):bool {
		return ($action->isCacheable() && $this->storage->hasCache($action));
	}

	/**
	 * 全てのキャッシュをクリア
	 *
	 * @access public
	 */
	public function clear () {
		$this->storage->clear();
	}
}
