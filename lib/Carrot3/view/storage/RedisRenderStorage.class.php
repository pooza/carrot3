<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.storage
 */

namespace Carrot3;

/**
 * Redisレンダーストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RedisRenderStorage implements RenderStorage {
	private $server;

	/**
	 * @access public
	 */
	public function __construct () {
		if (!extension_loaded('redis')) {
			throw new ViewException('redisモジュールがロードされていません。');
		}
		$this->server = new Redis;
		$this->server->select(BS_REDIS_DATABASES_RENDER);
	}

	/**
	 * キャッシュを返す
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return Tuple キャッシュ
	 */
	public function getCache (Action $action):Tuple {
		if ($data = $this->server[$action->digest()]) {
			return Tuple::create($data);
		}
	}

	/**
	 * キャッシュを削除
	 *
	 * @access public
	 * @param Action $action アクション
	 */
	public function removeCache (Action $action) {
		$this->server->delete($action->digest());
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
		$this->server[$view->getAction()->digest()] = $data;
	}

	/**
	 * キャッシュを持っているか？
	 *
	 * @access public
	 * @param Action $action アクション
	 * @return bool キャッシュを持っていたらTrue
	 */
	public function hasCache (Action $action):bool {
		return !!$this->server->exists($action->digest());
	}

	/**
	 * 全てのキャッシュをクリア
	 *
	 * @access public
	 */
	public function clear () {
		$this->server->clear();
	}
}
