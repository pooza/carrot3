<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class UtilsTest extends Test {
	public function execute () {
		$this->assert('isPathAbsolute_1', Utils::isPathAbsolute('/etc/hosts'));
		$this->assert('isPathAbsolute_2', !Utils::isPathAbsolute('www/.htaccess'));
		$this->assert('getUniqueID', Utils::getUniqueID() != Utils::getUniqueID());
		$this->assert('executeMethod_1', Utils::executeMethod(
			'Utils', 'isPathAbsolute', ['/etc/hosts']
		));
		$this->assert('executeMethod_2', !Utils::executeMethod(
			'Utils', 'isPathAbsolute', ['www/.htaccess']
		));
	}
}
