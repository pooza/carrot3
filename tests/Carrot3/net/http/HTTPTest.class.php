<?php
namespace Carrot3;

class HTTPTest extends Test {
	public function execute () {
		$http = new HTTP('www.b-shock.co.jp');
		try {
			$response = $http->sendGET('/');
		} catch (HTTPException $e) {
			$response = $e->getResponse();
		}
		$this->assert('status_301', $response->getStatus() == 301);
	}
}
