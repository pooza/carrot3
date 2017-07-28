<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTTPTest extends Test {
	public function execute () {
		$this->assert('__construct', $http = new HTTP('www.b-shock.co.jp'));
		try {
			$response = $http->sendGET('/');
		} catch (HTTPException $e) {
			$response = $e->getResponse();
		}
		$this->assert('status_301', $response->getStatus() == 301);
	}
}
