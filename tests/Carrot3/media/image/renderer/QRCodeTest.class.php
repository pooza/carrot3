<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class QRCodeTest extends Test {
	public function execute () {
		$renderer = new QRCode;
		$renderer->setData('aaa');
		$this->assert('getContents', !!$renderer->getContents());

		$renderer = new QRCode;
		$renderer->setData('http://www.google.com/');
		$this->assert('getContents', !!$renderer->getContents());
	}
}
