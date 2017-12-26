<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PlatformTest extends Test {
	public function execute () {
		$platform = $this->controller->getPlatform();
		$this->assert('getPlatform', $platform instanceof Platform);
		$this->assert('getName', !!$platform->getName());

		$platform = Platform::create('freebsd');
		$this->assert('create_FreeBSD', $platform instanceof Platform);
		$this->assert('getProcessOwner_FreeBSD', $platform->getProcessOwner() == 'www');

		$platform = Platform::create('ubuntu');
		$this->assert('create_Ubuntu', $platform instanceof Platform);
		$this->assert('getProcessOwner_Ubuntu', $platform->getProcessOwner() == 'www-data');
	}
}
