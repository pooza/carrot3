<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CSSParserTest extends Test {
	public function execute () {
		$dir = DirectoryLayout::getInstance()['root'];
		$file = $dir->getEntry('www/carrotlib/css/carrot.css', 'CSSFile');
		$parser = new CSSParser($file->getContents());
		$this->assert('value', $parser['input,textarea']['ime-mode'] == 'active');
	}
}
