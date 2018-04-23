<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session.storage
 */

namespace Carrot3;

/**
 * memcacheセッションストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MemcacheSessionStorage implements SessionStorage {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return bool
	 */
	public function initialize ():bool {
		if (!MemcacheManager::getInstance()->isEnabled()) {
			return false;
		}
		$path = new StringFormat('%s:%s');
		$path[] = BS_MEMCACHE_DEFAULT_HOST;
		$path[] = BS_MEMCACHE_DEFAULT_PORT;
		ini_set('session.save_handler', 'memcached');
		ini_set('session.save_path', $path->getContents());
		return true;
	}
}
