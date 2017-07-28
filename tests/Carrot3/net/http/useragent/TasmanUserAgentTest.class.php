<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TasmanUserAgentTest extends Test {
	public function execute () {
		// IE 5.23
		$useragent = UserAgent::create(
			'Mozilla/4.0 (compatible; MSIE 5.23; Mac_PowerPC)'
		);
		$this->assert('create', $useragent instanceof TasmanUserAgent);
		$this->assert('isLegacy', $useragent->isLegacy());
	}
}
