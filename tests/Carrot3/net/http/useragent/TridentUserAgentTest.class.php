<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TridentUserAgentTest extends Test {
	public function execute () {
		// IE 5.5
		$useragent = UserAgent::create(
			'Mozilla/4.0 (compatible; MSIE 5.5; Windows 95)'
		);
		$this->assert('create_IE55', $useragent instanceof TridentUserAgent);
		$this->assert('getVersion_IE55', $useragent->getVersion() == 5.5);
		$this->assert('isLegacy_IE55', $useragent->isLegacy());

		// IE6
		$useragent = UserAgent::create(
			'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)'
		);
		$this->assert('create_IE6', $useragent instanceof TridentUserAgent);
		$this->assert('getVersion_IE6', $useragent->getVersion() == 6);
		$this->assert('isLegacy_IE6', $useragent->isLegacy());

		// IE10
		$useragent = UserAgent::create(
			'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)'
		);
		$this->assert('create_IE10', $useragent instanceof TridentUserAgent);
		$this->assert('getVersion_IE10', $useragent->getVersion() == 10);
	}
}
