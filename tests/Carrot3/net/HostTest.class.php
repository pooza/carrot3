<?php
namespace Carrot3;

class HostTest extends Test {
	public function execute () {
		$host = new Host('www.b-shock.co.jp');
		$this->assert('getName', $host->getName() == 'www.b-shock.co.jp');
		$this->assert('getAddress', $host->getAddress() == '153.126.164.179');
		$this->assert('getImageFile', $host->getImageFile('favicon') instanceof ImageFile);
		$this->assert('getImageInfo', $host->getImageInfo('favicon') instanceof Tuple);
	}
}
