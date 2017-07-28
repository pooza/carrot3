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
	}
}
