<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PhoneNumberValidatorTest extends Test {
	public function execute () {
		$this->assert('__construct', $validator = new PhoneNumberValidator);
		$this->assert('execute', $validator->execute('00-0000-0000'));
		$this->assert('execute', !$validator->execute('00-0000-00000'));
		$this->assert('execute', !$validator->execute('00000000000'));

		$this->assert('__construct', $validator = new PhoneNumberValidator);
		$validator->initialize([
			'loose' => true,
		]);
		$this->assert('execute', $validator->execute('00-0000-0000'));
		$this->assert('execute', $validator->execute('0000000000'));

		$this->request['tel1'] = '00';
		$this->request['tel2'] = '0000';
		$this->request['tel3'] = '0000';
		$this->assert('__construct', $validator = new PhoneNumberValidator);
		$validator->initialize([
			'fields' => ['tel1', 'tel2', 'tel3'],
		]);
		$this->assert('execute', $validator->execute(null));
	}
}
