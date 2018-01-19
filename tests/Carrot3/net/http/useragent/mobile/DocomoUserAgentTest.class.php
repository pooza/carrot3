<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DocomoUserAgentTest extends Test {
	public function execute () {
		$useragent = UserAgent::create('DoCoMo/1.0/SH506iC/c20/TB/W24H12');
		$display = $useragent->getDisplayInfo();
		$this->assert('getDisplayInfo_width_SH506iC', $display['width'] == 240);
	}
}
