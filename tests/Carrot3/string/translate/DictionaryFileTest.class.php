<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DictionaryFileTest extends Test {
	public function execute () {
		$dictionary = FileUtils::getDirectory('dictionaries')->getEntry('carrot');
		$this->assert('getDictionaryName', $dictionary->getDictionaryName() == 'Carrot3\DictionaryFile.carrot');
		$this->assert('translate', $dictionary->translate('email', 'ja') == 'メールアドレス');
	}
}
