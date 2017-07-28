<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class UserAgentTest extends Test {
	public function execute () {
		$useragent = UserAgent::create(null, 'default');
		$this->assert('create_Default', $useragent instanceof DefaultUserAgent);
		$this->assert('isSmartPhone_Default', !$useragent->isSmartPhone());
	}
}
