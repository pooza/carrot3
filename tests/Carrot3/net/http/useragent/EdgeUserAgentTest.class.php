<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EdgeUserAgentTest extends Test {
	public function execute () {
		$useragent = UserAgent::create(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393'
		);
		$this->assert('create', $useragent instanceof EdgeUserAgent);
		$this->assert('isChrome', !$useragent->isChrome());
		$this->assert('isEdge', $useragent->isEdge());
		$this->assert('isSafari', !$useragent->isSafari());
	}
}
