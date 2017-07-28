<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GeckoUserAgentTest extends Test {
	public function execute () {
		// Firefox 0.10
		$useragent = UserAgent::create(
			'Mozilla/5.0 (X11; U; Linux i686; rv:1.7.3) Gecko/20040913 Firefox/0.10'
		);
		$this->assert('create_Fx010', $useragent instanceof GeckoUserAgent);
		$this->assert('getVersion_Fx010', $useragent->getVersion() == '1.7.3');
		$this->assert('isLegacy_Fx010', $useragent->isLegacy());

		// Firefox 3.0
		$useragent = UserAgent::create(
			'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9) Gecko/2008051206 Firefox/3.0'
		);
		$this->assert('create_Fx3', $useragent instanceof GeckoUserAgent);
		$this->assert('getVersion_Fx3', $useragent->getVersion() == '1.9');
		$this->assert('isLegacy_Fx3', !$useragent->isLegacy());
		$this->assert('hasSupport_flash_Fx3', $useragent->hasSupport('flash'));
	}
}
