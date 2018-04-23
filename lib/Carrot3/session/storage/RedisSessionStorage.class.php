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
	 * @return bool
	 */
	public function initialize ():bool {
		if (!extension_loaded('redis')) {
			return false;
		}
		$url = URL::create(null, 'tcp');
		$url['host'] = BS_REDIS_HOST;
		$url['port'] = BS_REDIS_PORT;
		$url->setParameter('database', BS_REDIS_DATABASES_SESSION);
		ini_set('session.save_handler', 'redis');
		ini_set('session.save_path', $url->getContents());
		return true;
	}
}
