<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class EnglishValidatorTest extends Test {
	public function execute () {
		$this->assert('__construct', $validator = new EnglishValidator);
		$this->assert('execute', $validator->execute('english'));
		$this->assert('execute', $validator->execute("\n"));
		$this->assert('execute', !$validator->execute('日本語'));
	}
}
