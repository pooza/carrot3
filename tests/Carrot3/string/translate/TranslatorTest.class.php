<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TranslatorTest extends Test {
	public function execute () {
		$translator = Translator::getInstance();
		$this->assert('iterator', $translator->getIterator() instanceof \Iterator);
		$this->assert('createTuple', $translator->createTuple(['show', 'hide']) instanceof Tuple);
	}
}
