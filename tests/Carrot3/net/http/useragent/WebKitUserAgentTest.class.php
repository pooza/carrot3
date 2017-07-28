<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WebKitUserAgentTest extends Test {
	public function execute () {
		// Safari 0.8
		$useragent = UserAgent::create(
			'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; ja-jp) AppleWebKit/85.7 (KHTML, like Gecko) Safari/85.6'
		);
		$this->assert('create_Safari08', $useragent instanceof WebKitUserAgent);
		$this->assert('getVersion_Safari08', $useragent->getVersion() == '85.7');
		$this->assert('isLegacy_Safari08', $useragent->isLegacy());

		// Safari 5.0
		$useragent = UserAgent::create(
			'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; ja-jp) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16'
		);
		$this->assert('create_Safari5', $useragent instanceof WebKitUserAgent);
		$this->assert('getVersion_Safari5', $useragent->getVersion() == '533.16');
		$this->assert('isLegacy_Safari5', !$useragent->isLegacy());
		$this->assert('hasSupport_flash_Safari5', $useragent->hasSupport('flash'));
	}
}
