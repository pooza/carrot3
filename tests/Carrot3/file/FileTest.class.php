<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FileTest extends Test {
	public function execute () {
		$dir = FileUtils::getDirectory('sample');

		$file = $dir->getEntry('dirty.csv');
		$this->assert('analyzeType_csv', $file->analyzeType() == 'text/csv');

		$file = $dir->getEntry('spam.eml');
		$this->assert('analyzeType_eml', $file->analyzeType() == 'message/rfc822');

		$file = $dir->getEntry('sample.mov');
		$this->assert('analyzeType_mov', $file->analyzeType() == 'video/quicktime');
	}
}
