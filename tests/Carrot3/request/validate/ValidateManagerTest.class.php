<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ValidateManagerTest extends Test {
	public function execute () {
		$this->assert('getInstance', $manager = ValidateManager::getInstance());
		$validator = $manager->createValidator('date');
		$this->assert('createValidator1', $validator instanceof DateValidator);
		$validator = $manager->createValidator('largefile');
		$this->assert('createValidator2_1', $validator instanceof FileValidator);
		$this->assert('createValidator2_2', $validator['size'] == 1024);
	}
}
