<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MemcacheSerializeStorageTest extends Test {
	public function execute () {
		$storage = new MemcacheSerializeStorage;
		if ($storage->initialize()) {
			$key = Utils::getClass($this);
			$storage->setAttribute($key, '木の水晶球');
			$this->assert('getAttribute_1', ($storage->getAttribute($key) == '木の水晶球'));
			$storage->removeAttribute($key);
			$this->assert('getAttribute_2', StringUtils::isBlank($storage->getAttribute($key)));
		}
	}
}
