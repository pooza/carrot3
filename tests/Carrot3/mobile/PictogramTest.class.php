<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PictogramTest extends Test {
	public function execute () {
		$this->assert('getInstance', $picto = Pictogram::getInstance('晴れ'));
		$this->assert('getID', $picto->getID() == 63647);
		$this->assert('getNumericReference', $picto->getNumericReference() == '&#63647;');
	}
}
