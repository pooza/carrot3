<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CurlHTTPTest extends Test {
	public function execute () {
		$this->assert('__construct', $http = new CurlHTTP('www.b-shock.co.jp', 443));
		try {
			$response = $http->sendGET('/NotFound');
		} catch (HTTPException $e) {
			$response = $e->getResponse();
		}
		$this->assert('status_404', $response->getStatus() == 404);
		$this->assert('content-length_404', !!$response->getRenderer()->getSize());
	}
}
