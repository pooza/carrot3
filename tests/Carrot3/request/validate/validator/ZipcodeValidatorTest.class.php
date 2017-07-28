<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ZipcodeValidatorTest extends Test {
	public function execute () {
		$this->assert('__construct', $validator = new ZipcodeValidator);
		$this->assert('execute', $validator->execute('000-0000'));
		$this->assert('execute', !$validator->execute('0000000'));
		$this->assert('execute', !$validator->execute('000-00000'));

		$this->request['zipcode1'] = '000';
		$this->request['zipcode2'] = '0000';
		$this->assert('__construct', $validator = new ZipcodeValidator);
		$validator->initialize([
			'fields' => ['zipcode1', 'zipcode2'],
		]);
		$this->assert('execute', $validator->execute(null));
	}
}
