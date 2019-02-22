<?php
namespace Carrot3;

class RedisRenderStorage implements RenderStorage {
	private $server;

	public function __construct () {
		if (!extension_loaded('redis')) {
			throw new ViewException('redisモジュールがロードされていません。');
		}
		$this->server = new Redis;
		$this->server->select(BS_REDIS_DATABASES_RENDER);
	}

	public function getCache (Action $action):?Tuple {
		if ($data = $this->server[$action->digest()]) {
			return Tuple::create($data);
		}
		return null;
	}

	public function removeCache (Action $action) {
		$this->server->delete($action->digest());
	}

	public function cache (HTTPResponse $view) {
		$data = ['headers' => [], 'contents' => $view->getRenderer()->getContents()];
		foreach ($view->getHeaders() as $header) {
			if ($header->isVisible() && $header->isCacheable()) {
				$data['headers'][$header->getName()] = $header->getContents();
			}
		}
		$this->server[$view->getAction()->digest()] = $data;
	}

	public function hasCache (Action $action):bool {
		try {
			return !!$this->server->exists($action->digest());
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function clear () {
		$this->server->clear();
	}
}
