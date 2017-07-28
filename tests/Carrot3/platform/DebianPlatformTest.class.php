<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DebianPlatformTest extends Test {
	public function execute () {
		$platform = Platform::create('debian');
		$this->assert('create', $platform instanceof Platform);
		$this->assert('getProcessOwner', $platform->getProcessOwner() == 'www-data');
	}
}
