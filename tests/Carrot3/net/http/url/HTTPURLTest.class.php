<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTTPURLTest extends Test {
	public function execute () {
		$url = URL::create('http://www.b-shock.co.jp/');
		$this->assert('getImageFile', $url->getImageFile('favicon') instanceof ImageFile);
		$this->assert('getImageFile', $url->getImageFile('qr') instanceof ImageFile);
		$this->assert('getImageInfo', $url->getImageInfo('favicon') instanceof Tuple);
		$this->assert('getImageInfo', $url->getImageInfo('qr') instanceof Tuple);
	}
}
