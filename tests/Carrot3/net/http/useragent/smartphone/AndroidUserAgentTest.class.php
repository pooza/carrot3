<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class AndroidUserAgentTest extends Test {
	public function execute () {
		// Xperia SO-01B
		$useragent = UserAgent::create(
			'Mozilla/5.0 (Linux; U; Android 1.6; ja-jp; SonyEricssonSO-01B Build/R1EA018) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1'
		);
		$this->assert('create_Xperia', $useragent instanceof AndroidUserAgent);
		$this->assert('isSmartPhone_Xperia', $useragent->isSmartPhone());
		$this->assert('isLegacy_Xperia', !$useragent->isLegacy());

		// Optimus Pad
		$useragent = UserAgent::create(
			'Mozilla/5.0 (Linux; U; Android 3.0.1; ja-jp; L-06C Build/HRI66) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13'
		);
		$this->assert('create_OptimusPad', $useragent instanceof AndroidUserAgent);
		$this->assert('isTablet_OptimusPad', $useragent->isTablet());
	}
}
