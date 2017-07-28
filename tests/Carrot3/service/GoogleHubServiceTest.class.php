<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleHubServiceTest extends Test {
	public function execute () {
		$url = URL::create('https://www.b-shock.co.jp/');
		$this->assert('__construct', $service = new GoogleHubService);
		$this->assert('publish', $service->publish($url));
	}
}
