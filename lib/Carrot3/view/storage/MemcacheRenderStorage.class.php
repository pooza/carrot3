<?php
namespace Carrot3;

class MemcacheRenderStorage implements RenderStorage {
	private $memcache;

	public function __construct () {
		$this->memcache = MemcacheManager::getInstance()->getServer('render');
	}

	public function getCache (Action $action):?Tuple {
		if ($data = $this->memcache[$action->digest()]) {
			return Tuple::create((new PHPSerializer)->decode($data));
		}
		return null;
	}

	public function removeCache (Action $action) {
		$this->memcache->delete($action->digest());
	}

	public function cache (HTTPResponse $view) {
		$data = ['headers' => [], 'contents' => $view->getRenderer()->getContents()];
		foreach ($view->getHeaders() as $header) {
			if ($header->isVisible() && $header->isCacheable()) {
				$data['headers'][$header->getName()] = $header->getContents();
			}
		}
		$this->memcache[$view->getAction()->digest()] = (new PHPSerializer)->encode($data);
	}

	public function hasCache (Action $action):bool {
		return !!$this->memcache[$action->digest()];
	}

	public function clear () {
		if (!$this->memcache->getAttribute('error')) {
			$this->memcache->clear();
		}
	}
}
