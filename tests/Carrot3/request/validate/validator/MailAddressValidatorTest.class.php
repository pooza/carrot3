<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MailAddressValidatorTest extends Test {
	public function execute () {
		$validator = new MailAddressValidator;
		$this->assert('execute', $validator->execute('pooza@b-shock.org'));
		$this->assert('execute', !$validator->execute('pooza.@b-shock.org'));
	}
}
