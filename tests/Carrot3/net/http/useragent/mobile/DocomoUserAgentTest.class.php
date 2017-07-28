<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DocomoUserAgentTest extends Test {
	public function execute () {
		$useragent = UserAgent::create('DoCoMo/1.0/SH506iC/c20/TB/W24H12');
		$this->assert('hasSupport_cookie_SH506iC', !$useragent->hasSupport('cookie'));
		$this->assert('getVersion_SH506iC', $useragent->getVersion() == 1);
		$display = $useragent->getDisplayInfo();
		$this->assert('getDisplayInfo_width_SH506iC', $display['width'] == 240);

		$useragent = UserAgent::create('DoCoMo/2.0 P07A3(c500;TB;W24H15)');
		$this->assert('hasSupport_cookie_P07A3', $useragent->hasSupport('cookie'));
		$this->assert('getVersion_P07A3', $useragent->getVersion() == 2);
		$display = $useragent->getDisplayInfo();
		$this->assert('getDisplayInfo_width_P07A3', $display['width'] == 480);
	}
}
