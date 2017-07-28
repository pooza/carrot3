<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WindowsPhoneUserAgentTest extends Test {
	public function execute () {
		// ASUS Galaxy
		$useragent = UserAgent::create(
			'Mozilla/4.0 (compatible; MSIE 7.0; Windows Phone OS 7.0; Trident/3.1; IEMobile/7.0) Asus;Galaxy6'
		);
		$this->assert('create_ASUS_Galaxy', $useragent instanceof WindowsPhoneUserAgent);
		$this->assert('isSmartPhone_ASUS_Galaxy', $useragent->isSmartPhone());
		$this->assert('hasSupport_flash_ASUS_Galaxy', !$useragent->hasSupport('flash'));
	}
}
