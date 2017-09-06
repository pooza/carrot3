<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class BlinkUserAgentTest extends Test {
	public function execute () {
		$useragent = UserAgent::create(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36'
		);
		$this->assert('create', $useragent instanceof BlinkUserAgent);
		$this->assert('isChrome', $useragent->isChrome());
		$this->assert('isEdge', !$useragent->isEdge());
		$this->assert('isSafari', !$useragent->isSafari());
	}
}
