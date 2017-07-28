<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class KanaValidatorTest extends Test {
	public function execute () {
		$this->assert('__construct', $validator = new KanaValidator);
		$this->assert('execute', $validator->execute('アイウエオ'));
		$this->assert('execute', $validator->execute('あいうえお'));
		$this->assert('execute', !$validator->execute('english'));
		$this->assert('execute', $validator->execute("\n"));
	}
}
