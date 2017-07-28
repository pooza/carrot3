<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MIMETypeTest extends Test {
	public function execute () {
		$types = MIMEType::getInstance();
		$this->assert('count_0', 0 < $types->count());
		$this->assert('getType_1', MIMEType::getType('.txt') == 'text/plain');
		$this->assert('getType_2', MIMEType::getType('txt') == 'text/plain');
		$this->assert('getType_3', MIMEType::getType('.html') == 'text/html');
		$this->assert('getType_4', MIMEType::getType('.htm') == 'text/html');
		$this->assert('getType_6', MIMEType::getType('.ZIP') == 'application/zip');
		$this->assert('getSuffix_1', MIMEType::getSuffix('text/plain') == '.txt');
		$this->assert('getSuffix_2', MIMEType::getSuffix('application/unknown') == null);
	}
}
