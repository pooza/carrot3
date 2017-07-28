<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WrongCharactersRequestFilterTest extends Test {
	public function execute () {
		$this->assert('__construct', $filter = new WrongCharactersRequestFilter);

		$this->request[Utils::getClass($this)] = '㈱';
		$filter->execute();
		$this->assert('convert', $this->request[Utils::getClass($this)] == '(株)');
	}
}
