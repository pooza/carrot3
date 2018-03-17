<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HostTest extends Test {
	public function execute () {
		$host = new Host('www.b-shock.co.jp');
		$this->assert('getName', $host->getName() == 'www.b-shock.co.jp');
		$this->assert('getAddress', $host->getAddress() == '49.212.211.238');
		$this->assert('getImageFile', $host->getImageFile('favicon') instanceof ImageFile);
		$this->assert('getImageInfo', $host->getImageInfo('favicon') instanceof Tuple);
	}
}
