<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class JPEGMediaConvertorTest extends Test {
	public function execute () {
		$convertor = new JPEGMediaConvertor;
		if ($file = FileUtils::getDirectory('sample')->getEntry('sample.mov')) {
			$source = $file->copyTo(FileUtils::getDirectory('tmp'), 'MovieFile');
			$dest = $convertor->execute($source);
			$this->assert('analyzeType', ($dest->analyzeType() == 'image/jpeg'));
			$source->delete();
			$dest->delete();
		}
	}
}
