<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MemcacheTest extends Test {
	public function execute () {
		if ($memcache = MemcacheManager::getInstance()->getServer()) {
			$memcache['hoge'] = 1;
			$this->assert('get_1', ($memcache['hoge'] == 1));
			$memcache->delete('hoge');
			$this->assert('get_2', ($memcache['hoge'] === false));
		}
	}
}
