<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminMemcache
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminMemcacheModule;
use \Carrot3 as C;

class SummaryAction extends C\Action {

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		return 'Memcacheの状態';
	}

	public function execute () {
		$manager = C\MemcacheManager::getInstance();
		$this->request->setAttribute('servers', C\Tuple::create());
		foreach ($manager->getServerNames() as $name) {
			if ($server = C\MemcacheManager::getInstance()->getServer($name)) {
				$this->request->getAttribute('servers')[$name] = $server->getAttributes();
			}
		}
		return C\View::SUCCESS;
	}
}
