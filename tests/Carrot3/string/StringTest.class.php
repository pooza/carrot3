<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class StringTest extends Test {
	public function execute () {
		$string = " \r\n   test\n   ";
		$this->assert('trim', StringUtils::trim($string) == "test");

		$string = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0";
		$this->assert('split', StringUtils::split($string, 38) == "Mozilla/5.0 (Macintosh; Intel Mac OS X\n 10.12; rv:54.0) Gecko/20100101 Firefo\nx/54.0");
		$this->assert('split-flowed', StringUtils::split($string, 38, true) == "Mozilla/5.0 (Macintosh; Intel Mac OS X \n 10.12; rv:54.0) Gecko/20100101 Firefo \nx/54.0");
	}
}
