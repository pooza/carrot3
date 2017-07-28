<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PrestoUserAgentTest extends Test {
	public function execute () {
		// Opera 10
		$useragent = UserAgent::create(
			'Opera/9.80 (Macintosh; Intel Mac OS X; U; en) Presto/2.2.15 Version/10.00'
		);
		$this->assert('create_Opera10', $useragent instanceof PrestoUserAgent);
		$this->assert('getVersion_Opera10', $useragent->getVersion() == '2.2.15');
		$this->assert('isLegacy_Opera10', !$useragent->isLegacy());
		$this->assert('hasSupport_flash_Opera10', $useragent->hasSupport('flash'));
	}
}
