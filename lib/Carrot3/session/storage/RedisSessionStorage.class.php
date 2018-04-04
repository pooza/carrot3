<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session.storage
 */

namespace Carrot3;

/**
 * Redisセッションストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RedisSessionStorage implements SessionStorage {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		if (!MemcacheManager::getInstance()->isEnabled()) {
			return false;
		}
		$path = new StringFormat('tcp://%s:%d?database=%d');
		$path[] = BS_REDIS_HOST;
		$path[] = BS_REDIS_PORT;
		$path[] = BS_REDIS_DATABASES_SESSION;
		ini_set('session.save_handler', 'redis');
		ini_set('session.save_path', $path->getContents());
		return true;
	}
}
